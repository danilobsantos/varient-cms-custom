(function () {
    'use strict';

    let state = {
        page: VRCommentConfig.initialPage || 1,
        isLoading: false,
        sortBy: 'newest'
    };

    let pendingSubmission = null;
    let captchaModal = null;

    /**
     * Helper to retrieve the latest CSRF token securely.
     */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="X-CSRF-TOKEN"]');
        return meta ? meta.getAttribute('content') : VRCommentConfig.csrfHash;
    }

    // INITIALIZATION
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Bootstrap Modal for Captcha if exists
        const captchaEl = document.getElementById('captchaModal');
        if (captchaEl) captchaModal = new bootstrap.Modal(captchaEl);

        // Visual Focus Handling for Inputs
        document.addEventListener('focusin', function (e) {
            if (e.target.matches('.editor-textarea, .input-minimal')) {
                const container = e.target.closest('.editor-container');
                if (container) {
                    container.classList.add('focused');
                    container.classList.remove('is-invalid');
                }
            }
        });

        // Global Event Delegation (Centralized Click Handler)
        document.addEventListener('click', handleGlobalClicks);

        // Load More Button Listener
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) loadMoreBtn.addEventListener('click', () => loadComments(false));

        // Main Form Submit Listener
        const mainForm = document.getElementById('mainCommentForm');
        if (mainForm) mainForm.addEventListener('submit', handleMainSubmit);

        // Captcha Verify Button Listener
        const verifyBtn = document.getElementById('verifyCaptchaBtn');
        if (verifyBtn) verifyBtn.addEventListener('click', verifyAndSubmit);

        // Inline Reply Forms (Delegated Submit)
        document.addEventListener('submit', function (e) {
            if (e.target.classList.contains('reply-form-inline')) handleInlineReplySubmit(e);
        });

        // Sorting Options
        document.querySelectorAll('.sort-option').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                state.sortBy = this.getAttribute('data-sort');
                state.page = 1;
                document.getElementById('sortBtnText').innerText = this.innerText;
                loadComments(true);
            });
        });
    });

    /**
     * Universal Fetch Wrapper
     * Handles AJAX requests with FormData and standard error catching
     */
    async function fetchAPI(url, data) {
        const formData = new FormData();
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                formData.append(key, data[key]);
            }
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });

            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return await res.json();
        } catch (e) {
            console.error('Fetch Error:', e);
            return {status: 'error', message: VRCommentConfig.text.error || 'Connection error'};
        }
    }

    // CAPTCHA & SUBMISSION
    function showCaptcha() {
        if (!captchaModal) return;
        captchaModal.show();
        // Check if Captcha library is loaded to prevent errors
        if (typeof VrCaptcha !== 'undefined') {
            setTimeout(() => VrCaptcha.render('captchaContainer'), 100);
        } else {
            console.warn('VrCaptcha library is missing.');
        }
    }

    function verifyAndSubmit() {
        let token = (typeof VrCaptcha !== 'undefined') ? VrCaptcha.getToken() : null;

        if (!token) {
            const container = document.getElementById('captchaContainer');
            const oldErr = container.querySelector('.captcha-error');
            if (oldErr) oldErr.remove();

            let err = document.createElement('div');
            err.className = 'captcha-error text-danger small mt-2';
            err.innerHTML = '<i class="vri vri-shield me-1"></i> ' +
                (VRCommentConfig.text.botVerificationFailed || "Verification failed.");
            container.appendChild(err);
            return;
        }

        if (pendingSubmission) {
            pendingSubmission.data['g-recaptcha-response'] = token;
            executeSubmission();
        }
    }

    async function executeSubmission() {
        if (!pendingSubmission) return;
        if (captchaModal) captchaModal.hide();

        const {data, btn, formType, formRef, parentId} = pendingSubmission;
        const originalHtml = btn.innerHTML;

        // Loading State
        btn.disabled = true;
        btn.innerHTML = `${btn.textContent.trim()} <span class="spinner-border spinner-border-sm ms-1"></span>`;

        const res = await fetchAPI(VRCommentConfig.endpoints.add, data);

        if (res.status === 'success') {
            if (res.comment_status === 'pending') {
                resetForms(formType, formRef);
                Swal.fire({
                    icon: 'success',
                    text: res.message,
                    confirmButtonText: VRCommentConfig.text.yes || 'OK'
                });
            } else if (res.comment_status === 'approved' && res.html) {
                if (formType === 'main') {
                    document.getElementById('commentsList').insertAdjacentHTML('afterbegin', res.html);
                    resetForms('main');
                    updateCommentCount(1);
                } else {
                    appendReply(parentId, res.html);
                    resetForms('reply', formRef);
                }
            }
        } else {
            // Handle validation errors (array or string)
            let msg = res.message || 'Error occurred';
            if (res.messages && typeof res.messages === 'object') {
                msg = Object.values(res.messages)[0];
            }

            Swal.fire({
                text: msg,
                icon: 'error',
                confirmButtonText: VRCommentConfig.text.ok,
            });
        }

        btn.disabled = false;
        btn.innerHTML = originalHtml;
        pendingSubmission = null;
    }

    // FORM HANDLERS & VALIDATION
    function validateForm(content, name, email, isInline) {
        let valid = true;
        const container = isInline ? content.closest('.editor-container') : document.getElementById('mainEditorContainer');

        if (!content.value.trim()) {
            container.classList.add('is-invalid');
            valid = false;
        } else {
            container.classList.remove('is-invalid');
        }

        if (!VRCommentConfig.isLoggedIn) {
            if (!name.value.trim()) {
                name.classList.add('is-invalid');
                valid = false;
            } else name.classList.remove('is-invalid');

            if (!email.value.trim() || !email.value.includes('@')) {
                email.classList.add('is-invalid');
                valid = false;
            } else email.classList.remove('is-invalid');
        }

        if (!valid) {
            container.classList.add('shake');
            setTimeout(() => container.classList.remove('shake'), 500);
        }
        return valid;
    }

    async function handleMainSubmit(e) {
        e.preventDefault();

        if (!verifyContentAccess()) return;

        const content = document.getElementById('commentContent');
        const name = document.getElementById('guestName');
        const email = document.getElementById('guestEmail');

        if (!validateForm(content, name, email, false)) return;

        const data = prepareFormData(0, content.value, name?.value, email?.value);
        const btn = document.getElementById('submitBtn');
        processSubmission(data, btn, 'main');
    }

    async function handleInlineReplySubmit(e) {
        e.preventDefault();

        if (!verifyContentAccess()) return;

        const form = e.target;
        const parentId = form.getAttribute('data-parent');
        const content = form.querySelector('textarea');
        const name = form.querySelector('input[type="text"]');
        const email = form.querySelector('input[type="email"]');
        const btn = form.querySelector('.btn-post-comment');

        if (!validateForm(content, name, email, true)) return;

        const data = prepareFormData(parentId, content.value, name?.value, email?.value);
        processSubmission(data, btn, 'reply', form, parentId);
    }

    function prepareFormData(parentId, comment, name, email) {
        return {
            post_id: VRCommentConfig.postId,
            parent_id: parentId,
            comment: comment,
            name: !VRCommentConfig.isLoggedIn ? name : '',
            email: !VRCommentConfig.isLoggedIn ? email : '',
            [VRCommentConfig.csrfTokenName]: getCsrfToken(),
            form_load_time: document.querySelector('input[name="form_load_time"]')?.value || ''
        };
    }

    function processSubmission(data, btn, formType, formRef = null, parentId = 0) {
        // Ensure captcha status is treated as integer
        const isCaptchaActive = (parseInt(VRCommentConfig.captchaStatus) === 1);
        pendingSubmission = {data, btn, formType, formRef, parentId};

        if (!VRCommentConfig.isLoggedIn && isCaptchaActive) {
            showCaptcha();
        } else {
            executeSubmission();
        }
    }

    // UTILITIES
    function updateCommentCount(delta) {
        let countEl = document.getElementById('totalCommentsCount');
        if (countEl) countEl.innerText = Math.max(0, parseInt(countEl.innerText) + delta);
    }

    function resetForms(type, formRef) {
        if (type === 'main') {
            document.getElementById('mainCommentForm').reset();
            document.getElementById('mainEditorContainer')?.classList.remove('focused');
        } else if (formRef) {
            formRef.reset();
            formRef.closest('.inline-reply-wrapper').style.display = 'none';
        }
    }

    function appendReply(parentId, html) {
        const parent = document.getElementById('comment-' + parentId);
        if (!parent) return;
        const body = parent.querySelector('.comment-body');
        let list = body.querySelector('.replies-list');

        if (!list) {
            // Create container if it's the first reply
            let container = body.querySelector('.thread-line-container');
            if (!container) {
                body.insertAdjacentHTML('beforeend', '<div class="thread-line-container"><div class="replies-list"></div></div>');
                list = body.querySelector('.replies-list');
            } else {
                list = container.querySelector('.replies-list');
            }
        }
        list.insertAdjacentHTML('beforeend', html);
    }

    // GLOBAL ACTIONS & EVENT HANDLERS
    function handleGlobalClicks(e) {
        const t = e.target;

        // Outside Click Handler (Blur Editor)
        if (!t.closest('#mainCommentForm') && !t.closest('.modal') && !t.closest('.swal2-container')) {
            document.getElementById('mainEditorContainer')?.classList.remove('focused');
        }

        // Like Button
        const likeBtn = t.closest('.btn-like');
        if (likeBtn) {
            e.preventDefault();
            handleLike(likeBtn);
        }

        // Reply Toggle Button
        const replyBtn = t.closest('.btn-reply');
        if (replyBtn) {
            e.preventDefault();

            if (!verifyContentAccess()) return;

            const id = replyBtn.getAttribute('data-id');
            const wrapper = document.getElementById('reply-form-wrapper-' + id);
            if (wrapper) {
                const isVisible = wrapper.style.display === 'block';
                // Optional: Close other open forms for cleaner UX
                document.querySelectorAll('.inline-reply-wrapper').forEach(el => el.style.display = 'none');

                wrapper.style.display = isVisible ? 'none' : 'block';
                if (!isVisible) setTimeout(() => wrapper.querySelector('textarea')?.focus(), 100);
            }
        }

        // Cancel Reply Button
        const cancelBtn = t.closest('.btn-cancel-reply');
        if (cancelBtn) {
            e.preventDefault();
            const wrapper = document.getElementById('reply-form-wrapper-' + cancelBtn.getAttribute('data-target'));
            if (wrapper) wrapper.style.display = 'none';
        }

        // Delete Button (Standardized)
        const deleteBtn = t.closest('.btn-delete');
        if (deleteBtn) {
            e.preventDefault();
            confirmDelete(deleteBtn.getAttribute('data-id'));
        }

        // Show More Replies Button
        const showRepliesBtn = t.closest('.btn-show-replies');
        if (showRepliesBtn) {
            e.preventDefault();
            if (showRepliesBtn.previousElementSibling) showRepliesBtn.previousElementSibling.style.display = 'block';
            showRepliesBtn.remove();
        }
    }

    function confirmDelete(id) {
        Swal.fire({
            text: VRCommentConfig.text.confirmComment,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: VRCommentConfig.text.yes,
            cancelButtonText: VRCommentConfig.text.cancel,
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                const data = {comment_id: id, [VRCommentConfig.csrfTokenName]: getCsrfToken()};
                const res = await fetchAPI(VRCommentConfig.endpoints.delete, data);
                if (res.status === 'success') {
                    const el = document.getElementById('comment-' + id);
                    if (el) {
                        // Smooth removal animation
                        el.style.opacity = '0';
                        el.style.transform = 'translateX(20px)';
                        el.style.transition = 'all 0.4s ease';
                        setTimeout(() => el.remove(), 400);
                        updateCommentCount(-1);
                    }
                }
            }
        });
    }

    async function handleLike(btn) {
        if (btn.disabled) return;

        if (!verifyContentAccess()) return;

        const id = btn.getAttribute('data-id');
        const icon = btn.querySelector('i');
        let count = parseInt(btn.innerText.trim()) || 0;
        const isLiked = btn.classList.contains('liked');

        // Optimistic UI Update (Instant Feedback)
        if (isLiked) {
            btn.classList.remove('liked');
            icon?.classList.replace('vri-heart-fill', 'vri-heart');
            btn.innerHTML = '';
            if (icon) btn.append(icon);
            btn.append(' ' + (count - 1));
        } else {
            btn.classList.add('liked');
            icon?.classList.replace('vri-heart', 'vri-heart-fill');
            btn.innerHTML = '';
            if (icon) btn.append(icon);
            btn.append(' ' + (count + 1));
        }

        const data = {comment_id: id, [VRCommentConfig.csrfTokenName]: getCsrfToken()};
        const res = await fetchAPI(VRCommentConfig.endpoints.like, data);

        // Revert UI if server request fails
        if (res.status !== 'success') {
            if (isLiked) {
                btn.classList.add('liked');
                icon?.classList.replace('vri-heart', 'vri-heart-fill');
                btn.innerHTML = '';
                if (icon) btn.append(icon);
                btn.append(' ' + count);
            } else {
                btn.classList.remove('liked');
                icon?.classList.replace('vri-heart-fill', 'vri-heart');
                btn.innerHTML = '';
                if (icon) btn.append(icon);
                btn.append(' ' + count);
            }
        }
    }

    async function loadComments(isReload = false) {
        if (state.isLoading) return;
        state.isLoading = true;

        const btn = document.getElementById('loadMoreBtn');
        const icon = btn?.querySelector('.btn-icon-svg');
        const list = document.getElementById('commentsList');

        if (isReload) {
            list.classList.add('processing-overlay');
            if (btn) btn.style.display = 'none';
        } else if (btn) {
            btn.disabled = true;
            icon?.classList.add('icon-spin');
        }

        const data = {
            post_id: VRCommentConfig.postId,
            limit: VRCommentConfig.perPage,
            offset: (state.page - 1) * VRCommentConfig.perPage,
            sort: state.sortBy,
            [VRCommentConfig.csrfTokenName]: getCsrfToken()
        };

        const res = await fetchAPI(VRCommentConfig.endpoints.load, data);

        if (res.status === 'success') {
            const countEl = document.getElementById('totalCommentsCount');
            if (countEl) countEl.innerText = res.total;

            if (isReload) {
                list.innerHTML = res.html || '<div class="text-center text-muted p-4">No comments yet.</div>';
                list.classList.remove('processing-overlay');
            } else if (res.html) {
                list.insertAdjacentHTML('beforeend', res.html);
            }
            state.page++;

            if (btn) {
                // Determine if there are more comments to load
                const totalLoaded = (state.page - 1) * VRCommentConfig.perPage;
                btn.disabled = false;
                btn.style.display = (totalLoaded < res.total) ? 'flex' : 'none';
            }
        }

        icon?.classList.remove('icon-spin');
        state.isLoading = false;
    }

    // Checks if the user has content access before any interactive action
    function verifyContentAccess() {
        if (VRCommentConfig.hasAccess) {
            return true;
        }

        // Determine which modal to show
        const modalId = (VRCommentConfig.restrictionType === 'exclusive')
            ? 'exclusiveActionModal'
            : 'premiumActionModal';

        const modalEl = document.getElementById(modalId);
        if (modalEl) {
            const actionModal = new bootstrap.Modal(modalEl);
            actionModal.show();
        }
        return false;
    }

})();