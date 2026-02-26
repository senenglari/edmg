<script>
    function scrollBottom() {
        $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
    }

    function loadUsers() {
        $.ajax({
            url: '{{ route('chat.users') }}',
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('.ajax-loading').addClass('active');
            },
            success: function (users) {
                let html = '';
                if (users.length === 0) {
                    html = '<div class="text-muted text-center" style="padding-top: 20px">Tidak ditemukan users.</div>';
                } else {
                    users.forEach(function (user) {
                        html += `
                            <div class="chat-item" data-id="${user.id}" data-name="${user.name}">
                                <div class="chat-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="chat-info">
                                    <strong>${user.name}</strong>
                                    <small>${user.last_message ?? ''}</small>
                                </div>
                        `;

                        if (user.unread_count > 0) {
                            html += `
                                <span style="margin-left:auto">
                                      ${user.unread_count > 0 ? `<span class="badge badge-danger unread-span" data-id="${user.id}">${user.unread_count}</span>` : ''}
                                </span>
                            `;
                        }
                        html += `</div>`;
                    });
                }
                $('.chatUserList').empty().html(html);
            },
            error: function (err) {
                console.log(err);
                alert('ada error ' + err.message);
                $('.ajax-loading').removeClass('active');
            },
            complete: function () {
                console.log('selesai');
            }
        });
    }

    function renderMessages(res) {
        $("#conversationId").val(res.conversation_id);

        let html = '';

        if (res.messages.length === 0) {
            html = '<div class="text-muted text-center">Belum ada pesan</div>';
        } else {
            $.each(res.messages, function (i, msg) {
                let cls = msg.sender_id === '{{auth()->id()}}' ? 'other' : 'me';
                html += `
                    <div class="message ${cls}">
                        <div>${msg.message}</div>
                        <small style="font-size:11px;color:#777;display:block;margin-top:4px;">
                            ${msg.created_at}
                        </small>
                    </div>
                `;
            });
        }

        $('#chatMessages').html(html);
        scrollBottom();
    }

    function appendMessage(msg) {
        let cls = msg.sender_id === '{{auth()->id()}}' ? 'me' : 'other';

        let html = `
            <div class="message ${cls}">
                <div>${msg.message}</div>
                <small style="font-size:11px;color:#777;display:block;margin-top:4px;">
                    ${msg.created_at}
                </small>
            </div>
        `;

        $('#chatMessages').append(html);
        scrollBottom();
    }

    $(document).on('click', 'div.chat-item', function () {
        $('.chat-item').removeClass('active');
        $(this).addClass('active');

        let user_id = $(this).data('id');

        $('#chatUserName').text($(this).data('name'));
        $('#chatMessages').html('Loading...');

        // enable chat input
        $('#messageInput')
            .prop('disabled', false)
            .attr('placeholder', 'Tulis pesan...');

        $('#sendMessage').prop('disabled', false);

        $.ajax({
            url: '{{ route("chat.open") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_id: user_id
            },
            dataType: 'json',
            success: function (res) {
                renderMessages(res);
                idUserUnread = $(".unread-span").data('id');
                if (user_id === idUserUnread) {
                    $(".unread-span").empty();
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    });

    $('#sendMessage').on('click', function () {
        let msg = $('#messageInput').val().trim();
        let conversationId = $('#conversationId').val();

        if (!msg || !conversationId) return;

        // disable sementara
        $('#sendMessage').prop('disabled', true);
        $('#typing').fadeIn(150);

        $.ajax({
            url: '{{ route("chat.send") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                conversation_id: conversationId,
                message: msg
            },
            dataType: 'json',
            success: function (res) {
                appendMessage(res);
                $('#messageInput').val('');
            },
            complete: function () {
                $('#typing').fadeOut(150);
                $('#sendMessage').prop('disabled', false);
            },
            error: function (err) {
                console.log(err);
                alert('Gagal mengirim pesan');
            }
        });
    });

    $('#messageInput').on('keypress', function (e) {
        if (e.which === 13) $('#sendMessage').click();
    });

    $('#openChat').on('click', function () {
        $('#chatBox').addClass('show');
        $('#openChat').addClass('hide');
        loadUsers();
    });

    $('#closeChat').on('click', function () {
        $('#chatBox').removeClass('show');
        $('#openChat').removeClass('hide');
        $('#chatMessages').empty();

        $('#messageInput')
            .prop('disabled', true)
            .val('')
            .attr('placeholder', 'Pilih percakapan terlebih dahulu');

        $('#sendMessage').prop('disabled', true);
    });

    $(document).on('click', '.chat-item', function () {
        $('.chat-wrapper').addClass('hide-sidebar');
    });

    $(document).on('click', '.mobile-back', function () {
        $('.chat-wrapper').removeClass('hide-sidebar');
    });
</script>