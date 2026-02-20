(function () {
    const input = document.getElementById("item_image");
    const img = document.getElementById("itemImagePreview");
    const placeholder = document.getElementById("itemImagePlaceholder");

    const qty = document.getElementById("item_quantity");
    const cost = document.getElementById("purchase_unit_cost");

    function apply() {
        if (!qty || !cost) return;

        const q = Number(qty.value || 0);
        cost.required = q > 0;
        cost.placeholder = q > 0 ? "0.00 (required)" : "0.00";
    }
    if (qty && cost) {
        qty.addEventListener("input", apply);
        apply();
    }
    if (!input || !img || !placeholder) return;

    function clearPreview() {
        img.removeAttribute("src");
        img.style.display = "none";
        placeholder.style.display = "block";
    }

    function showPreview(file) {
        const url = URL.createObjectURL(file);
        img.src = url;
        img.style.display = "block";
        placeholder.style.display = "none";

        // cleanup old object URLs to avoid memory leaks
        img.onload = () => URL.revokeObjectURL(url);
    }

    clearPreview();

    input.addEventListener("change", function () {
        const file = this.files && this.files[0];
        if (!file) {
            clearPreview();
            return;
        } // user cancelled

        // only image files
        if (!file.type || !file.type.startsWith("image/")) {
            clearPreview();
            alert("Please select an image file.");
            this.value = "";
            return;
        }

        showPreview(file);
    });
})();
