(function () {
    // ── Add-contract assignment logic (workers/show) ─────────────────────
    const ncTotal = document.getElementById("nc_total");
    const ncMonths = document.getElementById("nc_months");
    const ncPreview = document.getElementById("nc_preview");
    const ncTotalBar = document.getElementById("nc-total-bar");
    const ncTotalDisplay = document.getElementById("nc-total-display");

    // Guard: these elements only exist on the show page
    if (!ncTotal || !ncMonths) return;

    function ncSumCosts() {
        let sum = 0;
        document
            .querySelectorAll("#addContractForm .nc-cost-input")
            .forEach((inp) => {
                const v = parseFloat(inp.value);
                if (!isNaN(v) && v > 0) sum += v;
            });
        return sum;
    }
    function ncRefreshTotal() {
        const sum = ncSumCosts();
        if (sum > 0) {
            ncTotalDisplay.textContent = "$" + sum.toFixed(2);
            ncTotalBar.style.display = "flex";
            ncTotal.value = sum.toFixed(2);
        } else {
            ncTotalBar.style.display = "none";
            ncTotal.value = "";
        }
        ncRefreshPreview();
    }
    function ncRefreshPreview() {
        const t = parseFloat(ncTotal.value),
            m = parseInt(ncMonths.value);
        ncPreview.value =
            t > 0 && m > 0 ? "$" + (t / m).toFixed(2) + " / mo" : "";
    }

    document.querySelectorAll(".nc-proj-cb").forEach((cb) => {
        cb.addEventListener("change", function () {
            const pid = this.dataset.projectId;
            const row = this.closest("[data-nc-proj-row]");
            const costDiv = row.querySelector(".nc-proj-cost");
            const costInp = costDiv.querySelector("input[type=number]");
            const hidInp = row.querySelector(".nc-proj-id-hidden");
            const aptRows = document.querySelectorAll(
                `.nc-apt-row[data-nc-apt-parent="${pid}"]`,
            );
            if (this.checked) {
                costDiv.style.display = "flex";
                costInp.disabled = false;
                hidInp.disabled = false;
                aptRows.forEach((row) => {
                    const aptCb = row.querySelector(".nc-apt-cb");
                    const aptCost = row.querySelector(".nc-apt-cost");
                    const aptInp = aptCost.querySelector("input[type=number]");
                    const aptHid = row.querySelector(".nc-apt-id-hidden");
                    row.style.opacity = ".3";
                    row.style.pointerEvents = "none";
                    aptCb.checked = false;
                    aptCost.style.display = "none";
                    if (aptInp) {
                        aptInp.value = "";
                        aptInp.disabled = true;
                    }
                    if (aptHid) aptHid.disabled = true;
                });
            } else {
                costDiv.style.display = "none";
                costInp.value = "";
                costInp.disabled = true;
                hidInp.disabled = true;
                aptRows.forEach((row) => {
                    row.style.opacity = "";
                    row.style.pointerEvents = "";
                });
            }
            ncRefreshTotal();
        });
    });

    document.querySelectorAll(".nc-apt-cb").forEach((cb) => {
        cb.addEventListener("change", function () {
            const costDiv =
                this.closest(".nc-apt-row").querySelector(".nc-apt-cost");
            const costInp = costDiv.querySelector("input[type=number]");
            const hidInp =
                this.closest(".nc-apt-row").querySelector(".nc-apt-id-hidden");
            costDiv.style.display = this.checked ? "flex" : "none";
            costInp.disabled = !this.checked;
            if (hidInp) hidInp.disabled = !this.checked;
            if (!this.checked) costInp.value = "";
            ncRefreshTotal();
        });
    });

    document
        .querySelectorAll("#addContractForm .nc-cost-input")
        .forEach((inp) => {
            inp.addEventListener("input", ncRefreshTotal);
        });

    ncTotal.addEventListener("input", ncRefreshPreview);
    ncMonths.addEventListener("input", ncRefreshPreview);
})();

(function () {
    // ── Create worker form assignment logic (workers/create) ─────────────
    const totalInput = document.getElementById("total_amount");
    const monthsInput = document.getElementById("payment_months");
    const preview = document.getElementById("monthly_preview");
    const totalDisplay = document.getElementById("totalDisplay");
    const totalBar = document.getElementById("totalBar");

    // Guard: these elements only exist on the create page
    if (!totalInput || !monthsInput) return;

    function sumAllCosts() {
        let sum = 0;
        document.querySelectorAll(".cost-input").forEach((inp) => {
            const v = parseFloat(inp.value);
            if (!isNaN(v) && v > 0) sum += v;
        });
        return sum;
    }

    function refreshTotal() {
        const sum = sumAllCosts();
        if (sum > 0) {
            totalDisplay.textContent = "$" + sum.toFixed(2);
            totalBar.style.display = "flex";
            totalInput.value = sum.toFixed(2);
        } else {
            totalBar.style.display = "none";
            totalInput.value = "";
        }
        refreshPreview();
    }

    function refreshPreview() {
        const t = parseFloat(totalInput.value);
        const m = parseInt(monthsInput.value);
        preview.value =
            t > 0 && m > 0 ? "$" + (t / m).toFixed(2) + " / month" : "";
    }

    document.querySelectorAll(".proj-cb").forEach((cb) => {
        cb.addEventListener("change", function () {
            const pid = this.dataset.projectId;
            const costEl =
                this.closest(".assign-row").querySelector(".assign-cost");
            const costInp = costEl.querySelector("input[type=number]");
            const aptRows = document.querySelectorAll(
                `.assign-row--apt[data-parent="${pid}"]`,
            );
            if (this.checked) {
                costInp.disabled = false;
                costEl.classList.add("is-visible");
                this.closest(".assign-row").querySelector(
                    ".proj-id-hidden",
                ).disabled = false;
                aptRows.forEach((row) => {
                    const aptCb = row.querySelector(".apt-cb");
                    const aptCost = row.querySelector(".assign-cost");
                    const aptInp = aptCost.querySelector("input[type=number]");
                    const aptHid = row.querySelector(".apt-id-hidden");
                    row.classList.add("is-disabled");
                    aptCb.checked = false;
                    aptCost.classList.remove("is-visible");
                    if (aptInp) {
                        aptInp.value = "";
                        aptInp.disabled = true;
                    }
                    if (aptHid) aptHid.disabled = true;
                });
            } else {
                costInp.disabled = true;
                costInp.value = "";
                costEl.classList.remove("is-visible");
                this.closest(".assign-row").querySelector(
                    ".proj-id-hidden",
                ).disabled = true;
                aptRows.forEach((row) => row.classList.remove("is-disabled"));
            }
            refreshTotal();
        });
    });

    document.querySelectorAll(".apt-cb").forEach((cb) => {
        cb.addEventListener("change", function () {
            const costEl =
                this.closest(".assign-row").querySelector(".assign-cost");
            const inp = costEl.querySelector("input[type=number]");
            const hid =
                this.closest(".assign-row").querySelector(".apt-id-hidden");
            if (this.checked) {
                inp.disabled = false;
                costEl.classList.add("is-visible");
                if (hid) hid.disabled = false;
            } else {
                inp.disabled = true;
                inp.value = "";
                costEl.classList.remove("is-visible");
                if (hid) hid.disabled = true;
            }
            refreshTotal();
        });
    });

    // Disable all cost inputs on page load
    document
        .querySelectorAll(".cost-input")
        .forEach((inp) => (inp.disabled = true));

    document.querySelectorAll(".cost-input").forEach((inp) => {
        inp.addEventListener("input", refreshTotal);
    });

    totalInput.addEventListener("input", refreshPreview);
    monthsInput.addEventListener("input", refreshPreview);

    refreshTotal();
})();
