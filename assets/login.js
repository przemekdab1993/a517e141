import $ from 'jquery';

const loginAjax = () => {
    const $containerVote = $('.sign-up-form');
    const $errorBlock = $('#error-message');
    const $userInfo = $('#success-message');

console.log('wefwe');
    $containerVote.find('form').on('submit', function(event) {
        event.preventDefault();

        let dataSet = {
            username : event.target.username.value,
            password : event.target.password.value
        }

        $.ajax({
            url: `/login`,
            method: 'POST',
            headers: {
               'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                username: dataSet.username,
                password: dataSet.password,
            }),

            success: function (data, textStatus, request) {
                console.log('Submission was successful.');
                $errorBlock.text('');
                $errorBlock.addClass('d-none');

                $.ajax({
                    url: request.getResponseHeader('location'),
                    method: 'GET',
                    success: function (data, textStatus, request) {
                        $userInfo.text('Zostałeś poprawnie zalogowany jako '+data.username);
                        $userInfo.removeClass('d-none');

                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    }
                });
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log('An error occurred.');
                if (jqXHR.responseJSON) {
                    $errorBlock.text(jqXHR.responseJSON.error);
                    $errorBlock.removeClass('d-none');
                } else {
                    $errorBlock.text('Unknown error');
                    $errorBlock.removeClass('d-none');
                }
            },
        }).then(function(data) {
            //console.log(data.);
        });
    });
};

loginAjax();