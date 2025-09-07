document.addEventListener('DOMContentLoaded', function () {
    const timer = document.getElementById('timer');
    const form = document.getElementById('cbt-form');
    let totalSeconds = 600;

    function updateTimer() {
        let minutes = Math.floor(totalSeconds / 60);
        let seconds = totalSeconds % 60;
        timer.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        if (totalSeconds <= 0) {
            alert("⏰ Waktu habis! Jawaban Anda akan dikirim.");
            form.submit();
        }
        totalSeconds--;
    }

    setInterval(updateTimer, 1000);
});
