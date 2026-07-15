<?php
$models = ['Scheme', 'District', 'Subdivision', 'Block', 'Municipality', 'Panchayat'];
foreach ($models as $model) {
    $path = 'd:/Projects/samarth-payment-v1/app/Models/' . $model . '.php';
    if (!file_exists($path)) { echo "$model not found\n"; continue; }
    $content = file_get_contents($path);
    if (strpos($content, 'public function lotControl()') === false) {
        $content = preg_replace('/}(?![\s\S]*})/', "\n    public function lotControl()\n    {\n        return \$this->morphOne(\App\Models\LotControl::class, 'blockable');\n    }\n}", $content);
        file_put_contents($path, $content);
        echo "Added to $model\n";
    }
}
