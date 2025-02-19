$(document).ready(function() {
    $('.subscriptionForm').on('submit', function(e) {
        e.preventDefault();
        var email = $(this).find('input[name="email"]').val();

        $.ajax({
            type: 'POST',
            url: 'includes/subscribe.php',
            data: { email: email },
            dataType: 'json',
            success: function(response) {
                var resultDiv = $(e.target).next('#subscriptionResult');

                if (response.status === 'success') {
                    resultDiv.html('<div class="success">Email успешно подписан!</div>');
                    e.target.reset();
                } else if (response.status === 'exists') {
                    resultDiv.html('<div class="warning">Этот email уже подписан.</div>');
                } else {
                    resultDiv.html('<div class="error">Произошла ошибка. Попробуйте снова.</div>');
                }

                resultDiv.show();
                setTimeout(function() {
                    resultDiv.fadeOut();
                }, 3000);
            },
            error: function() {
                $(e.target).next('#subscriptionResult')
                    .html('<div class="error">Ошибка соединения. Попробуйте позже.</div>')
                    .show();
            }
        });
    });
});