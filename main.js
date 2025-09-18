/**
 * Atualiza a data e a hora na barra de informações a cada segundo.
 */
function updateTime() {
    const infoBar = document.getElementById('info-bar');
    // Se o elemento não existir na página, não faz nada.
    if (!infoBar) {
        return;
    }

    const now = new Date();
    const options = {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    };
    const formattedDate = now.toLocaleDateString('pt-BR', options).replace('de ', '').replace(' de', '');
    infoBar.textContent = `Londrina, ${formattedDate}`;
}

// Atualiza a hora a cada segundo (1000ms)
setInterval(updateTime, 1000);