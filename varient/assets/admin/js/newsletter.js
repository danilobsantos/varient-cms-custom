const translations = App.config.translations;

let selectedEmails = {
    users: [],
    newsletter: []
};

let emailQueue = {
    emails: [], subject: '', body: '', total: 0, sent: 0,
    batchSize: 5, // Send 5 emails per AJAX request
    isSending: false
};

function updateSelectedEmails(type) {
    const tableId = type === 'users' ? '#tableUsers' : '#tableNewsletter';
    selectedEmails[type] = $(`${tableId} input[name="id[]"]:checked`).map(function () {
        return $(this).data('email');
    }).get();

    $(`.selected-count[data-type="${type}"]`).text(selectedEmails[type].length);
}

$("#checkboxAllUsers").click(function () {
    $('#tableUsers tbody input[type="checkbox"]').prop('checked', this.checked);
    updateSelectedEmails('users');
});

$("#checkboxAllNewsletter").click(function () {
    $('#tableNewsletter tbody input[type="checkbox"]').prop('checked', this.checked);
    updateSelectedEmails('newsletter');
});

$(document).on('change', '#tableUsers input[name="id[]"]', () => updateSelectedEmails('users'));
$(document).on('change', '#tableNewsletter input[name="id[]"]', () => updateSelectedEmails('newsletter'));

const dataTables = {
    users: {
        currentPage: 0,
        searchQuery: '',
        isLoading: false,
        hasMore: true,
        debounceTimer: null,
        containerId: '#userTableContainer',
        inputId: '#searchUsers',
        spinnerId: '#spinnerUsers',
        ajaxUrl: generateUrl('Ajax/loadMoreUsers'),
        tableSelector: '#userTableContainer tbody'
    },
    newsletter: {
        currentPage: 0,
        searchQuery: '',
        isLoading: false,
        hasMore: true,
        debounceTimer: null,
        containerId: '#newsletterTableContainer',
        inputId: '#searchNewsletter',
        spinnerId: '#spinnerNewsletter',
        ajaxUrl: generateUrl('Ajax/loadMoreNewsletterEmails'),
        tableSelector: '#newsletterTableContainer tbody'
    }
};

function loadMore(type) {
    const config = dataTables[type];
    if (config.isLoading || !config.hasMore) return;

    $(config.spinnerId).show();
    config.isLoading = true;
    config.currentPage++;

    $.ajax({
        type: 'POST',
        url: config.ajaxUrl,
        data: {
            page: config.currentPage,
            q: config.searchQuery
        },
        success: function (response) {
            setTimeout(() => {
                if (response.result == 1) {
                    if (response.htmlContent && response.htmlContent !== '') {
                        $(config.tableSelector).append(response.htmlContent);
                    }

                    if (response.count === 0) {
                        config.hasMore = false;
                    }
                }
                config.isLoading = false;
                $(config.spinnerId).hide();
            }, 300);
        },
        error: function () {
            config.isLoading = false;
            $(config.spinnerId).hide();
        }
    });
}

function setupScrollLoading(type) {
    const config = dataTables[type];
    const container = document.querySelector(config.containerId);
    container.addEventListener('scroll', function () {
        if (container.scrollTop + container.clientHeight >= container.scrollHeight - 10 && !config.isLoading) {
            loadMore(type);
        }
    });
}

function setupSearchInput(type) {
    const config = dataTables[type];
    $(document).on('input', config.inputId, function () {
        clearTimeout(config.debounceTimer);
        const q = $(this).val().trim();
        config.debounceTimer = setTimeout(() => {
            config.searchQuery = q.length > 1 ? q : '';
            config.currentPage = 0;
            config.hasMore = true;
            $(config.tableSelector).empty();
            loadMore(type);
        }, 300);
    });
}

function deleteNewsletterEmail(id) {
    Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
        if (result.isConfirmed) {
            var data = {'id': id};
            $.ajax({
                type: 'POST',
                url: generateUrl("Admin/deleteNewsletterEmail"),
                data: data,
                success: function (response) {
                    $('#rowNewsletter' + id).remove();
                }
            });
        }
    });
};

const modalSendEmail = document.getElementById('modalSendEmail');

modalSendEmail.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const type = button.getAttribute('data-type');
    const emails = selectedEmails[type];

    emailQueue.type = type;
    emailQueue.emails = emails;

    if (emails.length === 0) {
        Swal.fire(swalOptions(translations.select_at_least_one_recipient, 'warning', true));
        event.preventDefault();
        return;
    }

    const receiverList = $('#receiverList');
    const receiverCount = $('#receiverCount');
    receiverList.empty();
    receiverCount.text(emails.length);

    const emailsToShow = emails.slice(0, 6);
    emailsToShow.forEach(email => {
        receiverList.append(`<span class="email-tag">${email}</span>`);
    });
    if (emails.length > 6) {
        receiverList.append(`<span class="email-tag fw-bold">+${emails.length - 6}</span>`);
    }
});

modalSendEmail.addEventListener('hidden.bs.modal', function (event) {
    $('#email_subject').val('');
    if (typeof tinymce !== 'undefined' && tinymce.get('email_body')) {
        tinymce.get('email_body').setContent('');
    }
    $('#emailFormContent').removeClass('d-none');
    $('#sendingProgress').addClass('d-none');
    $('#progressLog').empty();
    $('#btnSendEmail').removeAttr('data-kt-indicator');
    $('#btnSendEmail').prop('disabled', false);
    emailQueue.isSending = false;
});

$('#btnSendEmail').on('click', function () {
    if (emailQueue.isSending) return;

    const subject = $('#email_subject').val();
    const body = tinyMCE.activeEditor.getContent();

    if (!subject.trim() || !body.trim()) {
        Swal.fire(swalOptions(VrConfig.text.fillAllRequiredFields, 'warning', true));
        return;
    }

    emailQueue.subject = subject;
    emailQueue.body = body;
    emailQueue.total = emailQueue.emails.length;
    emailQueue.sent = 0;
    emailQueue.isSending = true;

    $(this).attr('data-kt-indicator', 'on');
    $(this).prop('disabled', true);
    $('#emailFormContent').addClass('d-none');
    $('#sendingProgress').removeClass('d-none');
    $('#progressLog').empty();

    sendEmailBatch();
});

function sendEmailBatch() {
    if (!emailQueue.isSending) {
        return;
    }

    if (emailQueue.sent >= emailQueue.total) {
        $('#progressStatusText').text(translations.completed);
        $('#btnSendEmail').removeAttr('data-kt-indicator');

        emailQueue.isSending = false;
        Swal.fire(swalOptions(translations.emails_sent_successfully, 'success', true));

        $('#email_subject').val('');
        let editor = tinymce.get('edt_newsletter') || tinymce.activeEditor;
        if (editor) {
            editor.setContent('');
        }

        return;
    }

    const batch = emailQueue.emails.slice(emailQueue.sent, emailQueue.sent + emailQueue.batchSize);

    const data = {
        subject: emailQueue.subject,
        body: emailQueue.body,
        emails: batch,
        type: emailQueue.type
    };

    $.ajax({
        type: 'POST',
        url: generateUrl("Admin/sendNewsletterBatch"),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                batch.forEach(email => {
                    $('#progressLog').prepend(`<div class="d-flex align-items-center text-success"><i class="ki-duotone ki-check-circle fs-4 text-success me-2"><span class="path1"></span><span class="path2"></span></i>${email}</div>`);
                });
            } else {
                batch.forEach(email => {
                    $('#progressLog').prepend(`<div class="d-flex align-items-center text-danger"><i class="ki-duotone ki-cross-circle fs-4 text-danger me-2"><span class="path1"></span><span class="path2"></span></i>${email} - ${response.error || 'Failed'}</div>`);
                });
            }
        },
        error: function () {
            batch.forEach(email => {
                $('#progressLog').prepend(`<div class="d-flex align-items-center text-danger"><i class="ki-duotone ki-cross-circle fs-4 text-danger me-2"><span class="path1"></span><span class="path2"></span></i>${email} - Server Error</div>`);
            });
        },
        complete: function () {
            emailQueue.sent += batch.length;
            let percentage = Math.round((emailQueue.sent / emailQueue.total) * 100);
            $('#progressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);
            $('#progressPercentage').text(percentage + '%');
            $('#progressStatusText').text(`${translations.sending}... ${emailQueue.sent} / ${emailQueue.total}`);
            sendEmailBatch();
        }
    });
}

$(document).ready(function () {
    ['users', 'newsletter'].forEach(type => {
        setupScrollLoading(type);
        setupSearchInput(type);
        loadMore(type);
    });
});