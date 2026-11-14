// Interactive UI helpers (runs on every page)
document.addEventListener("DOMContentLoaded", () => {
  // 1. messages fade out after 6 seconds
  document.querySelectorAll(".msg").forEach(m => setTimeout(() => { m.style.opacity = "0"; setTimeout(() => m.remove(), 500); }, 6000));

  // 2. numbers in stat cards count up
  document.querySelectorAll("[data-count]").forEach(el => {
    const end = +el.dataset.count; let n = 0; const step = Math.max(1, Math.ceil(end / 25));
    const t = setInterval(() => { n = Math.min(end, n + step); el.textContent = n; if (n >= end) clearInterval(t); }, 25);
  });

  // 3. live search for tables: <input data-filter="tableId">
  document.querySelectorAll("input[data-filter]").forEach(box => {
    const table = document.getElementById(box.dataset.filter);
    box.addEventListener("input", () => {
      const q = box.value.toLowerCase();
      table.querySelectorAll("tr").forEach((row, i) => { if (i > 0) row.style.display = row.textContent.toLowerCase().includes(q) ? "" : "none"; });
    });
  });

  // 4. password strength meter + "show password" checkbox
  document.querySelectorAll("input[data-rule='password']").forEach(inp => {
    const meter = document.createElement("div"); meter.className = "meter"; meter.innerHTML = "<span></span><small></small>";
    inp.insertAdjacentElement("afterend", meter);
    inp.addEventListener("input", () => {
      const v = inp.value; let s = 0;
      if (v.length >= 8) s++; if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
      const names = ["", "Weak", "Fair", "Good", "Strong"], cols = ["#ccc", "#b3261e", "#e0a100", "#3b82c4", "#1e7a46"];
      meter.firstChild.style.width = (v ? s * 25 : 0) + "%"; meter.firstChild.style.background = cols[s];
      meter.lastChild.textContent = v ? names[s] : "";
    });
  });
  document.querySelectorAll("form").forEach(f => {
    const pw = f.querySelectorAll("input[type=password]"); if (!pw.length) return;
    const lab = document.createElement("label"); lab.className = "showpw";
    lab.innerHTML = '<input type="checkbox"> Show password';
    lab.firstChild.addEventListener("change", e => pw.forEach(p => p.type = e.target.checked ? "text" : "password"));
    f.querySelector("button[type=submit], button").insertAdjacentElement("beforebegin", lab);
  });

  // 5. buttons show "Please wait" after a valid submit
  document.querySelectorAll("form").forEach(f => f.addEventListener("submit", ev => {
    if (!ev.defaultPrevented) { const b = f.querySelector("button[type=submit]"); if (b) setTimeout(() => { b.disabled = true; b.textContent = "Please wait..."; }, 0); }
  }));
});
