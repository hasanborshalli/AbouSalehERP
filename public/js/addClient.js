(function () {
    const totalPrice = document.getElementById("total_price");
    const discount = document.getElementById("discount");
    const downPayment = document.getElementById("down_payment");
    const months = document.getElementById("installment_months");
    const monthlyPayment = document.getElementById("installment_amount");
    const autoCalcBtn = document.getElementById("autoCalcBtn");

    const netPriceText = document.getElementById("netPriceText");
    const remainingText = document.getElementById("remainingText");
    const totalPaidText = document.getElementById("totalPaidText");

    function num(el) {
        const v = el ? parseFloat(el.value) : 0;
        return Number.isFinite(v) ? v : 0;
    }

    function money(v) {
        return "$" + (Math.round(v * 100) / 100).toFixed(2);
    }

    function calc() {
        const t = num(totalPrice);
        const d = num(discount);
        const net = Math.max(t - d, 0);

        const dp = num(downPayment);
        const rem = Math.max(net - dp, 0);

        const m = Math.max(parseInt(months?.value || "0", 10) || 0, 0);
        const mp = num(monthlyPayment);
        const totalPaid = dp + m * mp;

        if (netPriceText) netPriceText.textContent = money(net);
        if (remainingText) remainingText.textContent = money(rem);
        if (totalPaidText) totalPaidText.textContent = money(totalPaid);
    }

    function autoCalcMonthly() {
        const t = num(totalPrice);
        const d = num(discount);
        const net = Math.max(t - d, 0);

        const dp = num(downPayment);
        const rem = Math.max(net - dp, 0);

        const m = Math.max(parseInt(months?.value || "0", 10) || 0, 0);
        if (!m) return;

        const mp = rem / m;
        monthlyPayment.value = (Math.round(mp * 100) / 100).toFixed(2);
        calc();
    }

    [totalPrice, discount, downPayment, months, monthlyPayment].forEach(
        (el) => {
            if (!el) return;
            el.addEventListener("input", calc);
        },
    );

    if (autoCalcBtn) autoCalcBtn.addEventListener("click", autoCalcMonthly);

    calc();
})();
(function () {
    const sel = document.getElementById("apartment_id");
    if (!sel) return;

    const projectName = document.getElementById("project_name");
    const unitNumber = document.getElementById("unit_number");
    const location = document.getElementById("location");
    const totalPrice = document.getElementById("total_price");
    const aptFloor = document.getElementById("apt_floor");
    const aptArea = document.getElementById("apt_area");
    const aptBedrooms = document.getElementById("apt_bedrooms");
    const aptBathrooms = document.getElementById("apt_bathrooms");
    const aptNotes = document.getElementById("apt_notes");
    function setVal(el, v) {
        if (!el) return;
        el.value = (v ?? "").toString();
    }

    function fillFromSelected() {
        const opt = sel.selectedOptions && sel.selectedOptions[0];
        if (!opt) return;

        setVal(projectName, opt.dataset.projectName);
        setVal(unitNumber, opt.dataset.unitNumber);
        setVal(location, opt.dataset.location);

        // Optional: auto-fill total price if your apartment has price
        if (opt.dataset.price !== undefined && opt.dataset.price !== "") {
            setVal(totalPrice, opt.dataset.price);
        }
        setVal(aptFloor, opt.dataset.floor);
        setVal(aptArea, opt.dataset.area);
        setVal(aptBedrooms, opt.dataset.bedrooms);
        setVal(aptBathrooms, opt.dataset.bathrooms);
        if (aptNotes) aptNotes.value = (opt.dataset.notes ?? "").toString();
    }

    sel.addEventListener("change", fillFromSelected);

    // If page reloads with old selected value (validation error), refill
    fillFromSelected();
})();
