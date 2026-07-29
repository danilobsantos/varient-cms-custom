<style>
    html, body {
        margin: 0 auto !important;
        padding: 0 !important;
        height: 100% !important;
        width: 100% !important;
        font-family: <?= $fontFamily; ?>;
    }

    * {
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
    }

    table, td {
        mso-table-lspace: 0pt !important;
        mso-table-rspace: 0pt !important;
    }

    table {
        border-spacing: 0 !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
        margin: 0 auto !important;
    }

    img {
        -ms-interpolation-mode: bicubic;
    }

    a {
        text-decoration: none;
    }

    .text-body-content * {
        box-sizing: border-box;
        max-width: 100%;
    }

    .text-body-content p {
        margin: 0 0 15px 0;
    }

    .text-body-content p,
    .text-body-content span,
    .text-body-content li,
    .text-body-content div {
        font-size: 16px !important;
        line-height: 26px !important;
        font-family: <?= $fontFamily; ?> !important;
        word-break: break-word;
        overflow-wrap: anywhere;
        color: <?= $colorText; ?> !important;
    }

    .text-body-content a {
        font-size: 16px !important;
        line-height: 26px !important;
        font-family: <?= $fontFamily; ?> !important;
        word-break: break-word;
        overflow-wrap: anywhere;
        text-decoration: underline;
        color: <?= esc($templateColor); ?> !important;
    }

    .text-body-content h2 {
        font-size: 18px !important;
        line-height: 26px !important;
        font-weight: 600 !important;
        margin: 0 0 14px 0 !important;
        color: <?= $color; ?> !important;
    }

    .text-body-content h3 {
        font-size: 17px !important;
        line-height: 24px !important;
        font-weight: 600 !important;
        margin: 0 0 12px 0 !important;
        color: <?= $color; ?> !important;
    }

    .text-body-content h4,
    .text-body-content h5,
    .text-body-content h6 {
        font-size: 17px !important;
        line-height: 24px !important;
        font-weight: 600 !important;
        margin: 0 0 10px 0 !important;
        color: <?= $color; ?> !important;
    }

    .text-body-content img,
    .text-body-content video {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        margin-bottom: 15px;
    }

    .text-body-content iframe {
        width: 100% !important;
        max-width: 100% !important;
        margin-bottom: 15px;
    }

    .text-body-content ul,
    .text-body-content ol {
        margin: 0 0 15px 20px;
        padding: 0;
    }

    .text-body-content li {
        margin-bottom: 4px;
    }

    .text-body-content strong,
    .text-body-content b {
        font-weight: 600;
    }

    .text-body-content em,
    .text-body-content i {
        font-style: italic;
    }

    .text-body-content br {
        line-height: 26px;
    }

    @media screen and (max-width: 600px) {
        .mobile-padding {
            padding-left: 20px !important;
            padding-right: 20px !important;
        }

        .mobile-stack {
            display: block !important;
            width: 100% !important;
        }
    }
</style>