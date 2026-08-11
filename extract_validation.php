<?php

$seederPath = 'd:\Projects\samarth-payment-v1\database\seeders\CodemasterSeeder.php';
$seeder = file_get_contents($seederPath);

$targetParents = [
    'validation_lot_status',
    'validation_lot_configuration',
    'validation_mode'
];

$childsStr = "";

foreach ($targetParents as $parent) {
    preg_match_all('/array\(\s*"name"\s*=>\s*[^,]+,\s*"short_name"\s*=>\s*[^,]+,\s*"parent_short_code"\s*=>\s*"' . $parent . '",\s*"code"\s*=>\s*[^,]+,\s*\),/Uis', $seeder, $matches);
    
    foreach ($matches[0] as $match) {
        $childsStr .= "        " . $match . "\n";
        $seeder = str_replace($match, "", $seeder);
    }
}

$validationContent = "<?php\n\nreturn [\n    'childs' => [\n$childsStr    ]\n];\n";
file_put_contents('d:\Projects\samarth-payment-v1\config\validation.php', $validationContent);

// Add the merging logic
if (strpos($seeder, '$validationChilds = config(\'validation.childs\');') === false) {
    $seeder = preg_replace('/(\$encDetails = config\(\'encdetails\.childs\'\);)/i', "\$validationChilds = config('validation.childs');\n        if (is_array(\$validationChilds)) {\n            \$codemasterChilds = array_merge(\$codemasterChilds, \$validationChilds);\n        }\n        $1", $seeder);
}

file_put_contents($seederPath, $seeder);
echo "OK\n";
