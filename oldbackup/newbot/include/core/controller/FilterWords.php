<?php


class FilterWords
{
    private $words;
    private $warning_word;

    public function __construct($words)
    {
        $this->words = $words;
    }

    public function filterwords($text, $symbol = "*")
    {
        $filterCount = sizeof((array)$this->words);
        for ($i = 0; $i < $filterCount; $i++) {
            $text = preg_replace('[' . $this->words[$i] . ']', str_repeat($symbol, strlen('$0')), $text);
        }
        return $text;
    }

    public function wordsfilter($text, $hard_filter = true)
    {
        foreach ($this->words as $word) {
            if ($hard_filter) {
                if (str_contains($text, $word)) {
                    $this->warning_word = $word;
                    return false;
                }
            } else {
                $texts = explode(' ', $text);
                if (in_array($word, $texts)) {
                    $this->warning_word = $word;
                    return false;
                }
            }
        }
        return true;
    }

    public function getWarningWords()
    {
        return $this->warning_word;
    }
}