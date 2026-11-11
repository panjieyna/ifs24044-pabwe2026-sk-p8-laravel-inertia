<?php

namespace App\Features\Post\Enums;

enum PostCategory: string
{
    case TEKNOLOGI = 'Teknologi';
    case PENDIDIKAN = 'Pendidikan';
    case GAYA_HIDUP = 'Gaya Hidup';
    case BISNIS = 'Bisnis';
    case OPINI = 'Opini';
    case UMUM = 'Umum';

    /**
     * Label lengkap kategori untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::TEKNOLOGI => 'Teknologi & Koding',
            self::PENDIDIKAN => 'Pendidikan & Tutorial',
            self::GAYA_HIDUP => 'Gaya Hidup & Produktivitas',
            self::BISNIS => 'Bisnis & Karir',
            self::OPINI => 'Opini & Pemikiran',
            self::UMUM => 'Umum',
        };
    }

    /**
     * Kelas Tailwind untuk badge kategori (kontras teks memenuhi WCAG AA).
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::TEKNOLOGI => 'bg-blue-100 text-blue-800',
            self::PENDIDIKAN => 'bg-emerald-100 text-emerald-800',
            self::GAYA_HIDUP => 'bg-pink-100 text-pink-800',
            self::BISNIS => 'bg-amber-100 text-amber-900',
            self::OPINI => 'bg-violet-100 text-violet-800',
            self::UMUM => 'bg-slate-200 text-slate-800',
        };
    }

    /**
     * Daftar opsi kategori untuk frontend.
     *
     * @return array<int, array{value: string, label: string, badgeClass: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'badgeClass' => $case->badgeClass(),
        ], self::cases());
    }

    /**
     * Daftar nilai kategori yang valid.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Mencocokkan teks bebas (impor Excel / AI) ke kategori yang valid.
     */
    public static function fromLoose(?string $value): self
    {
        $needle = mb_strtolower(trim((string) $value));

        foreach (self::cases() as $case) {
            if ($needle === mb_strtolower($case->value) || $needle === mb_strtolower($case->label())) {
                return $case;
            }
        }

        return self::UMUM;
    }
}
