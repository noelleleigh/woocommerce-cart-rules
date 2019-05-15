# Delete the existing archive if it's there
if (Test-Path .\woocommerce-cart-rules.zip) {
    Remove-Item .\woocommerce-cart-rules.zip;
}
# Make a new one
Compress-Archive -Path .\woocommerce-cart-rules.php, .\readme.txt, .\LICENSE -DestinationPath .\woocommerce-cart-rules.zip;