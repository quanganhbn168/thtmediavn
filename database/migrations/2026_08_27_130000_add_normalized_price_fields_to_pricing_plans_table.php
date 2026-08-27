<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('pricing_plans', 'price_amount')) {
                $table->decimal('price_amount', 15, 2)->nullable()->after('price');
            }
            if (! Schema::hasColumn('pricing_plans', 'is_price_from')) {
                $table->boolean('is_price_from')->default(false)->after('price_amount');
            }
            if (! Schema::hasColumn('pricing_plans', 'price_unit')) {
                $table->string('price_unit', 80)->nullable()->after('is_price_from');
            }
        });

        foreach (\DB::table('pricing_plans')->select(['id', 'name', 'price'])->get() as $plan) {
            $normalized = $this->normalizePrice($plan->price);

            if ($normalized['price_amount'] === null) {
                continue;
            }

            \DB::table('pricing_plans')
                ->where('id', $plan->id)
                ->update($normalized);
        }

        // Profile được báo giá theo số trang, nên lưu đúng bản chất đơn giá.
        \DB::table('pricing_plans')
            ->where('name', 'Profile doanh nghiệp')
            ->update([
                'price_amount' => 250000,
                'is_price_from' => true,
                'price_unit' => 'trang',
            ]);
    }

    public function down(): void
    {
        // Pricing fields are retained to avoid destructive schema rollbacks.
    }

    /**
     * Convert existing display strings into normalized pricing fields.
     * Examples: 8.900.000đ, 12.000.000đ/tháng, Từ 250k/trang.
     *
     * @return array{price_amount: float|null, is_price_from: bool, price_unit: string|null}
     */
    private function normalizePrice(?string $price): array
    {
        $price = trim((string) $price);

        if ($price === '' || ! preg_match('/(?<amount>\d[\d.,]*)\s*(?<multiplier>k|nghìn|triệu)?/iu', $price, $matches, PREG_OFFSET_CAPTURE)) {
            return [
                'price_amount' => null,
                'is_price_from' => false,
                'price_unit' => null,
            ];
        }

        $amount = (float) str_replace(['.', ','], '', $matches['amount'][0]);
        $multiplier = strtolower((string) ($matches['multiplier'][0] ?? ''));

        if (in_array($multiplier, ['k', 'nghìn'], true)) {
            $amount *= 1000;
        } elseif ($multiplier === 'triệu') {
            $amount *= 1000000;
        }

        $matchedText = $matches[0][0];
        $matchedOffset = $matches[0][1];
        $remainder = substr($price, $matchedOffset + strlen($matchedText));
        $unit = preg_match('/\/\s*([^\s,;]+)/u', $remainder, $unitMatch)
            ? trim($unitMatch[1], " .,:;()[]{}")
            : null;

        return [
            'price_amount' => $amount,
            'is_price_from' => (bool) preg_match('/^\s*từ\b/iu', $price),
            'price_unit' => $unit !== '' ? $unit : null,
        ];
    }
};
