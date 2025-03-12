setInterval(function () {

    fetch('/marchant/all/end-user/balance/status', {
        method: 'GET', // or 'POST', 'PUT', 'DELETE'
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content // For Laravel
        }
    })
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));
}, 120000);