document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchTicket');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#ticketsTable tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});

function openModal() {
    document.getElementById('ticketModal').classList.add('active');
}

function closeModal() {
    document.getElementById('ticketModal').classList.remove('active');
}

window.onclick = function (event) {
    const modal = document.getElementById('ticketModal');
    if (event.target === modal) {
        closeModal();
    }
};