<?php

class NumberToWord
{

    protected $digit1 = array(
        0 => 'صفر',
        1 => 'اول',
        2 => 'دوم',
        3 => 'سوم',
        4 => 'چهارم',
        5 => 'پنجم',
        6 => 'ششم',
        7 => 'هفتم',
        8 => 'هشتم',
        9 => 'نهم',
    );
    protected $digit1_5 = array(
        1 => 'یازدهم',
        2 => 'دوازدهم',
        3 => 'سیزدهم',
        4 => 'چهاردهم',
        5 => 'پانزدهم',
        6 => 'شانزدهم',
        7 => 'هفدهم',
        8 => 'هجدهم',
        9 => 'نوزدهم',
    );
    protected $digit2 = array(
        1 => 'دهم',
        2 => 'بیستم',
        3 => 'سیم',
        4 => 'چهلم',
        5 => 'پنجاهم',
        6 => 'شصتم',
        7 => 'هفتادم',
        8 => 'هشتادم',
        9 => 'نودم'
    );
    protected $digit3 = array(
        1 => 'صدم',
        2 => 'دویستم',
        3 => 'سیصدم',
        4 => 'چهارصدم',
        5 => 'پانصدم',
        6 => 'ششصدم',
        7 => 'هفتصدم',
        8 => 'هشتصدم',
        9 => 'نهصدم',
    );
    protected $steps = array(
        1 => 'هزارم',
        2 => 'میلیونم',
        3 => 'بیلیونم',
        4 => 'تریلیونم',
        5 => 'کادریلیونم',
        6 => 'کوینتریلیونم',
        7 => 'سکستریلیونم',
        8 => 'سپتریلیونم',
        9 => 'اکتریلیونم',
        10 => 'نونیلیونم',
        11 => 'دسیلیونم',
    );
    protected $t = array(
        'and' => 'و',
    );

    public function number_format($number, $decimal_precision = 0, $decimals_separator = '.', $thousands_separator = ',')
    {
        $number = explode('.', str_replace(' ', '', $number));
        $number[0] = str_split(strrev($number[0]), 3);
        $total_segments = count($number[0]);
        for ($i = 0; $i < $total_segments; $i++) {
            $number[0][$i] = strrev($number[0][$i]);
        }
        $number[0] = implode($thousands_separator, array_reverse($number[0]));
        if (!empty($number[1])) {
            $number[1] = $this->Round($number[1], $decimal_precision);
        }
        return implode($decimals_separator, $number);
    }

    protected function groupToWords($group)
    {
        $d3 = floor($group / 100);
        $d2 = floor(($group - $d3 * 100) / 10);
        $d1 = $group - $d3 * 100 - $d2 * 10;

        $group_array = array();

        if ($d3 != 0) {
            $group_array[] = $this->digit3[$d3];
        }

        if ($d2 == 1 && $d1 != 0) { // 11-19
            $group_array[] = $this->digit1_5[$d1];
        } else if ($d2 != 0 && $d1 == 0) { // 10-20-...-90
            $group_array[] = $this->digit2[$d2];
        } else if ($d2 == 0 && $d1 == 0) { // 00
        } else if ($d2 == 0 && $d1 != 0) { // 1-9
            $group_array[] = $this->digit1[$d1];
        } else { // Others
            $group_array[] = $this->digit2[$d2];
            $group_array[] = $this->digit1[$d1];
        }

        if (!count($group_array)) {
            return FALSE;
        }

        return $group_array;
    }

    public function numberToWords($number)
    {
        $formated = $this->number_format($number, 0, '.', ',');
        $groups = explode(',', $formated);

        $steps = count($groups);

        $parts = array();
        foreach ($groups as $step => $group) {
            $group_words = self::groupToWords($group);
            if ($group_words) {
                $part = implode(' ' . $this->t['and'] . ' ', $group_words);
                if (isset($this->steps[$steps - $step - 1])) {
                    $part .= ' ' . $this->steps[$steps - $step - 1];
                }
                $parts[] = $part;
            }
        }
        return implode(' ' . $this->t['and'] . ' ', $parts);
    }

    /**
     * @var string[]
     */
    protected $numbers = [
        1 => 'یک',
        2 => 'دو',
        3 => 'سه',
        4 => 'چهار',
        5 => 'پنج',
        6 => 'شش',
        7 => 'هفت',
        8 => 'هشت',
        9 => 'نه',
        10 => 'ده',
    ];

    /**
     * @param int $number
     * @return string
     */
    public function NumbersToWord(int $number)
    {
        if (isset($this->numbers[$number])) return $this->numbers[$number];
        return '';
    }
}