<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class StructuredContent
{
    public static function translatedLines(array $translations): array
    {
        return collect($translations)->map(function (mixed $value): array {
            return collect(preg_split('/\R/u', (string) $value) ?: [])
                ->map(fn (string $line): string => trim($line))
                ->filter()
                ->values()
                ->all();
        })->all();
    }

    public static function translatedFaqs(array $translations): array
    {
        return collect($translations)->map(function (mixed $value): array {
            return collect(preg_split('/\R/u', (string) $value) ?: [])
                ->map(function (string $line): ?array {
                    [$question, $answer] = array_pad(explode('|', $line, 2), 2, '');
                    $question = trim($question);
                    $answer = trim($answer);

                    return $question !== '' && $answer !== '' ? compact('question', 'answer') : null;
                })
                ->filter()
                ->values()
                ->all();
        })->all();
    }

    public static function linesForForm(Model $model, string $field): array
    {
        return collect($model->getTranslations($field))
            ->map(fn (mixed $lines): string => collect(is_array($lines) ? $lines : [])->implode(PHP_EOL))
            ->all();
    }

    public static function faqsForForm(Model $model, string $field = 'faqs'): array
    {
        return collect($model->getTranslations($field))
            ->map(fn (mixed $faqs): string => collect(is_array($faqs) ? $faqs : [])
                ->map(fn (array $faq): string => trim((string) ($faq['question'] ?? '')).' | '.trim((string) ($faq['answer'] ?? '')))
                ->filter(fn (string $line): bool => $line !== ' | ')
                ->implode(PHP_EOL))
            ->all();
    }
}
