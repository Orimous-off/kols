$(document).ready(function() {
    $('#feedback').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var resultDiv = $('#subscriptionResult');
        var submitButton = form.find('button[type="submit"]');

        // Clear previous messages
        resultDiv.empty().hide();

        // Validate form
        var formData = form.serializeArray();
        var isValid = true;

        formData.forEach(function(input) {
            if (!input.value.trim()) {
                isValid = false;
            }
        });

        if (!isValid) {
            resultDiv.html('<div class="error">Пожалуйста, заполните все поля</div>').show();
            return;
        }

        // Disable submit button while processing
        submitButton.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: 'includes/feedback.php',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response && response.status === 'success') {
                    resultDiv.html('<div class="success">Вопрос отправлен, ожидайте звонка!</div>');
                    form[0].reset();
                } else {
                    var errorMessage = response && response.message ? response.message : 'Произошла ошибка. Попробуйте снова.';
                    resultDiv.html('<div class="error">' + errorMessage + '</div>');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Ошибка соединения. Попробуйте позже.';

                // Log detailed error information
                console.error('Ajax error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });

                resultDiv.html('<div class="error">' + errorMessage + '</div>');
            },
            complete: function() {
                // Re-enable submit button
                submitButton.prop('disabled', false);

                // Show result
                resultDiv.show();

                // Hide message after delay
                setTimeout(function() {
                    resultDiv.fadeOut();
                }, 3000);
            }
        });
    });
});