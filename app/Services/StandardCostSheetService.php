<?php

namespace App\Services;

use App\Models\StandardCostLine;
use App\Models\StandardCostSheet;
use Illuminate\Support\Collection;

class StandardCostSheetService
{
    public function calculate(StandardCostSheet $sheet): array
    {
        $sheet->loadMissing('lines.item', 'product');

        $lines = $sheet->lines->map(function (StandardCostLine $line) {
            $quantity = (float) $line->quantity;
            $unitPrice = (float) $line->unit_price_vnd;
            $wasteFactor = $line->category === 'material' ? 1 + ((float) $line->waste_pct / 100) : 1;
            $allocationQty = (float) ($line->allocation_qty ?? 0);
            $lineCost = $quantity * $unitPrice * $wasteFactor;

            if ($allocationQty > 0) {
                $lineCost /= $allocationQty;
            }

            $line->calculated_cost_vnd = $this->money($lineCost);
            $line->formula_text = $this->formulaText($line, $allocationQty);

            return $line;
        });

        $groupTotals = collect(StandardCostLine::CATEGORIES)
            ->mapWithKeys(fn($label, $key) => [$key => $lines->where('category', $key)->sum('calculated_cost_vnd')]);

        $productionCost = $this->money($groupTotals->sum());
        $salePrice = (float) $sheet->sale_price_vnd;
        $breakdown = $this->costBreakdown($sheet, $productionCost, $salePrice);
        $totalCost = $breakdown['total_cost_vnd'];
        $profit = $this->money($salePrice - $totalCost);
        $priceRounding = max(1, (float) ($sheet->price_rounding_vnd ?: 1));
        $breakEvenPrice = $this->roundPriceUp($this->solvePriceForMargin($sheet, $productionCost, 0), $priceRounding);
        $suggestedPrice = $this->roundPriceUp($this->solvePriceForMargin($sheet, $productionCost, (float) $sheet->target_margin_pct), $priceRounding);
        $quotePrice = $this->roundPriceUp($suggestedPrice * (1 + ((float) $sheet->vat_pct / 100)), $priceRounding);
        $suggestedBreakdown = $this->costBreakdown($sheet, $productionCost, $suggestedPrice);
        $suggestedProfit = $this->money($suggestedPrice - $suggestedBreakdown['total_cost_vnd']);

        return [
            'lines' => $lines,
            'groups' => $this->groupLines($lines, $groupTotals),
            'group_totals' => $groupTotals,
            'production_cost_vnd' => $productionCost,
            'bank_interest_vnd' => $breakdown['bank_interest_vnd'],
            'commission_vnd' => $breakdown['commission_vnd'],
            'management_vnd' => $breakdown['management_vnd'],
            'transport_vnd' => (float) $sheet->transport_cost_vnd,
            'total_cost_vnd' => $totalCost,
            'sale_price_vnd' => (float) $sheet->sale_price_vnd,
            'profit_vnd' => $profit,
            'margin_pct' => $sheet->sale_price_vnd > 0 ? round($profit / (float) $sheet->sale_price_vnd * 100, 2) : 0,
            'markup_pct' => $totalCost > 0 ? round($profit / $totalCost * 100, 2) : 0,
            'break_even_price_vnd' => $breakEvenPrice,
            'suggested_price_vnd' => $suggestedPrice,
            'quote_price_vnd' => $quotePrice,
            'suggested_profit_vnd' => $suggestedProfit,
            'suggested_margin_pct' => $suggestedPrice > 0 ? round($suggestedProfit / $suggestedPrice * 100, 2) : 0,
        ];
    }

    private function groupLines(Collection $lines, Collection $totals): Collection
    {
        return collect(StandardCostLine::CATEGORIES)->map(fn($label, $key) => [
            'key' => $key,
            'label' => $label,
            'lines' => $lines->where('category', $key)->values(),
            'total_vnd' => (float) ($totals[$key] ?? 0),
        ])->values();
    }

    private function percentageAmount(string $basis, float $pct, float $productionCost, float $salePrice, float $subtotal): float
    {
        $baseAmount = match ($basis) {
            'sale_price' => $salePrice,
            'subtotal' => $subtotal,
            default => $productionCost,
        };

        return $baseAmount * $pct / 100;
    }

    private function costBreakdown(StandardCostSheet $sheet, float $productionCost, float $salePrice): array
    {
        $bankInterest = $this->money($this->percentageAmount($sheet->bank_interest_basis, (float) $sheet->bank_interest_pct, $productionCost, $salePrice, $productionCost));
        $subtotalAfterBank = $this->money($productionCost + $bankInterest);
        $commission = $this->money($this->percentageAmount($sheet->commission_basis, (float) $sheet->commission_pct, $productionCost, $salePrice, $subtotalAfterBank));
        $subtotalAfterCommission = $this->money($subtotalAfterBank + $commission);
        $management = $this->money($this->percentageAmount($sheet->management_basis, (float) $sheet->management_pct, $productionCost, $salePrice, $subtotalAfterCommission));

        return [
            'bank_interest_vnd' => $bankInterest,
            'commission_vnd' => $commission,
            'management_vnd' => $management,
            'total_cost_vnd' => $this->money($subtotalAfterCommission + $management + (float) $sheet->transport_cost_vnd),
        ];
    }

    private function solvePriceForMargin(StandardCostSheet $sheet, float $productionCost, float $targetMarginPct): float
    {
        $targetMarginPct = min(95, max(0, $targetMarginPct));
        $isEnough = function (float $price) use ($sheet, $productionCost, $targetMarginPct): bool {
            if ($price <= 0) {
                return false;
            }

            $cost = $this->costBreakdown($sheet, $productionCost, $price)['total_cost_vnd'];

            return (($price - $cost) / $price * 100) >= $targetMarginPct;
        };

        $low = 0.0;
        $high = max(1, $productionCost * 2);
        while (! $isEnough($high) && $high < 1_000_000_000_000) {
            $high *= 2;
        }

        for ($i = 0; $i < 80; $i++) {
            $mid = ($low + $high) / 2;
            if ($isEnough($mid)) {
                $high = $mid;
            } else {
                $low = $mid;
            }
        }

        return $high;
    }

    private function roundPriceUp(float $price, float $step): float
    {
        return $this->money(ceil($price / $step) * $step);
    }

    private function formulaText(StandardCostLine $line, float $allocationQty): string
    {
        if ($line->category === 'material') {
            return $allocationQty > 0
                ? 'Định mức x đơn giá x (1 + hao hụt) / SL phân bổ'
                : 'Định mức x đơn giá x (1 + hao hụt)';
        }

        return $allocationQty > 0
            ? 'Hệ số x chi phí / SL phân bổ'
            : 'Số lượng x đơn giá';
    }

    private function money(float $value): float
    {
        return round($value, 4);
    }
}
