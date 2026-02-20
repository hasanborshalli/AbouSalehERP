(function () {
    const input = document.getElementById("item_image");
    const img = document.getElementById("itemImagePreview");
    const placeholder = document.getElementById("itemImagePlaceholder");

    if (!input || !img || !placeholder) return;

    function setStateFromCurrentImg() {
        if (img.getAttribute("src")) {
            img.style.display = "block";
            placeholder.style.display = "none";
        } else {
            img.style.display = "none";
            placeholder.style.display = "block";
        }
    }

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
        img.onload = () => URL.revokeObjectURL(url);
    }

    // ✅ initialize state for edit page
    setStateFromCurrentImg();

    input.addEventListener("change", function () {
        const file = this.files && this.files[0];
        if (!file) {
            setStateFromCurrentImg();
            return;
        }

        if (!file.type || !file.type.startsWith("image/")) {
            alert("Please select an image file.");
            this.value = "";
            setStateFromCurrentImg();
            return;
        }

        showPreview(file);
    });
})();
document
    .querySelector('[name="item_quantity"]')
    .addEventListener("input", function () {
        const checkbox = document.querySelector('[name="is_out_of_stock"]');
        checkbox.checked = Number(this.value) === 0;
    });
