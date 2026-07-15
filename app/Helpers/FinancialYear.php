<?php

namespace App\Helpers;

class FinancialYear
{
    /**
     * @var FinancialYear|null
     */
    private static $instance = null;

    /**
     * @var string
     */
    private $financialYear;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');
        
        $this->financialYear = $currentMonth >= 4 
            ? $currentYear . '-' . ($currentYear + 1) 
            : ($currentYear - 1) . '-' . $currentYear;
    }

    /**
     * Get the singleton instance.
     * 
     * @return FinancialYear
     */
    public static function getCurrentFinancialYear(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the computed financial year string.
     * 
     * @return string
     */
    public function get(): string
    {
        return $this->financialYear;
    }

    /**
     * Prevent cloning of the instance.
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance.
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
