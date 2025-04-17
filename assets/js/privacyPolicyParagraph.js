function handleBreakTag() {
    // Находим элемент <p> внутри .privacy-policy
    const privacyPolicyParagraph = document.querySelector('.privacy-policy p');

    if (!privacyPolicyParagraph) return; // Если элемент не найден, ничего не делаем

    // Получаем текущую ширину окна
    const screenWidth = window.innerWidth;
    console.log(screenWidth);

    // Сохраняем оригинальное содержимое, если его ещё нет
    if (!privacyPolicyParagraph.dataset.originalContent) {
        privacyPolicyParagraph.dataset.originalContent = privacyPolicyParagraph.innerHTML;
    }

    // Если ширина экрана <= 576px, удаляем <br>
    if (screenWidth <= 576) {
        console.log(1);
        const originalContent = privacyPolicyParagraph.dataset.originalContent;
        privacyPolicyParagraph.innerHTML = originalContent.replace(/<br\s*\/?>/gi, ' ');
        console.log(privacyPolicyParagraph.innerHTML);
    } else {
        // Если ширина > 576px, восстанавливаем оригинальное содержимое
        privacyPolicyParagraph.innerHTML = privacyPolicyParagraph.dataset.originalContent;
    }
}

// Выполняем функцию при загрузке страницы
document.addEventListener('DOMContentLoaded', handleBreakTag);

// Выполняем функцию при изменении размера окна
window.addEventListener('resize', handleBreakTag);