<?php

namespace App\Models\Concerns;

/** FAQ repeater stored as JSON: [{question, answer}]. */
trait HasFaqs
{
    public function initializeHasFaqs(): void
    {
        $this->mergeCasts(['faqs' => 'array']);
    }

    /** @return array<int, array{question: string, answer: string}> */
    public function faqList(): array
    {
        return collect($this->faqs ?? [])
            ->filter(fn ($faq) => filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->values()
            ->all();
    }
}
