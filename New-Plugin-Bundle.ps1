# Delete the existing archive if it's there
if (Test-Path .\woocommerce-cart-rules.zip) {
    Remove-Item .\woocommerce-cart-rules.zip;
}
# Make a new one
7z a woocommerce-cart-rules.zip .\woocommerce-cart-rules.php .\readme.txt;