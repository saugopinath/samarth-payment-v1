<?php
$files = [
    'd:/Projects/samarth-v1/app/Contracts/FormTabInterface.php',
    'd:/Projects/samarth-v1/app/FormTabs/BankDetailsTab.php',
    'd:/Projects/samarth-v1/app/FormTabs/ContactDetailsTab.php',
    'd:/Projects/samarth-v1/app/FormTabs/EnclosureListTab.php',
    'd:/Projects/samarth-v1/app/FormTabs/IdentificationNumbersTab.php',
    'd:/Projects/samarth-v1/app/FormTabs/PersonalDetailsTab.php',
    'd:/Projects/samarth-v1/app/FormTabs/SelfDeclarationTab.php',
];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('public function getValidationRules(): array', 'public function getValidationRules(?int $schemeId = null): array', $content);
        $content = str_replace('public function getValidationRules() : array', 'public function getValidationRules(?int $schemeId = null): array', $content);
        file_put_contents($file, $content);
    }
}
echo "Updated getValidationRules signature in all tabs.\n";
