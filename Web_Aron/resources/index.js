let kategoriakPage = 0;
let varosokPage = 0;
const kategoriakPerPage = 5;
const varosokPerPage = 5;

function lapoz(section, irany) {
    let pageVar, perPageVar, gridId, counterId;

    if (section === 'kategoriak') {
        pageVar = kategoriakPage;
        perPageVar = kategoriakPerPage;
        gridId = 'kategoriak-grid';
        counterId = 'kategoriak-counter';
    } else if (section === 'varosok') {
        pageVar = varosokPage;
        perPageVar = varosokPerPage;
        gridId = 'varosok-grid';
        counterId = 'varosok-counter';
    }

    const container = document.getElementById(gridId);
    const items = container.querySelectorAll('.grid-item');
    const totalPages = Math.ceil(items.length / perPageVar);

    pageVar += irany;
    if (pageVar < 0) pageVar = 0;
    if (pageVar >= totalPages) pageVar = totalPages - 1;

    items.forEach((item, index) => {
        if (index >= pageVar * perPageVar && index < (pageVar + 1) * perPageVar) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });

    document.getElementById(counterId).textContent = (pageVar + 1) + " / " + totalPages;

    if (section === 'kategoriak') {
        kategoriakPage = pageVar;
    } else if (section === 'varosok') {
        varosokPage = pageVar;
    }
}

window.addEventListener('DOMContentLoaded', () => {
    lapoz('kategoriak', 0);
    lapoz('varosok', 0);
});