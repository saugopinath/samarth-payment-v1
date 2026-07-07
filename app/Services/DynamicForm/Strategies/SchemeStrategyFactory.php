<?php

namespace App\Services\DynamicForm\Strategies;

use App\Models\Scheme;
use InvalidArgumentException;

class SchemeStrategyFactory
{
    /**
     * Resolve the appropriate strategy for a given scheme.
     * 
     * @param Scheme|int $scheme
     * @return SchemeStrategyInterface
     */
    public static function make($scheme): SchemeStrategyInterface
    {
        // Resolve by ID or short_name depending on what is passed
        // For demonstration, we assume we might get an ID or a Scheme model.
        
        $schemeId = $scheme instanceof Scheme ? $scheme->id : $scheme;
        
        // This mapping could also come from a config file or service provider
        // Avoiding if/else by using a lookup array or switch
        
        switch ($schemeId) {
            case 20:
                return new AnnapurnaBhandarStrategy();
            case 10:
                return new OldAgePensionStrategy();
            case 11:
                return new WidowPensionStrategy();
            case 2:
                return new WcdManabikStrategy();
            case 9:
                return new LppPensionerStrategy();
            case 8:
                return new LppRetainerStrategy();
            case 19:
                return new LegacyOldAgePensionForStStrategy();
            case 5:
                return new OldAgePensionForFishermanStrategy();
            case 7:
                return new TextilePensionStrategy();
            case 13:
                return new OldAgePensionForFarmerStrategy();
            case 17:
                return new StateWelfareSchemeForPurohitsStrategy();
            case 1:
                return new JaiJoharForStStrategy();
            case 6:
                return new MsmePensionStrategy();
            case 3:
                return new TaposiliBandhuForScStrategy();
            case 21:
                return new AnnapurnaYojanaStrategy();
            case 26:
                return new GopinathSauStrategy();
            default:
                throw new InvalidArgumentException("No Strategy defined for Scheme ID: {$schemeId}");
        }
    }
}
