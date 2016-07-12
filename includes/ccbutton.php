<?php

class CCButton {

    const CC_ICON_18px = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAABmJLR0QA/wD/AP+gvaeTAAACMElEQVQ4y5WUv0sbcRjGP5dLRjnbRQ1Ceqk4piHVyXbImsVMAcFkCQgSm5xa6B+guPoX2FCoZOoQKhkylv4B7WQcWigIbpUKQrx8vadL7jCxEvuM74+H93l/WfwbT4HnwCsgBQj4BXwFfgK/eQTeAqeAbNuW67pyXVe2bWtIeAq8m0TyEdDs7KxOTk7k+76MMTLGyPd9dTodJZPJkLD9EMkHy7K0ubmpSajX64rFYgI+A9Zdki1A1WpVj0W9Xg8ri2Q6wPfp6emRwMFgcC953DYzMxP27AlABlCr1ZIkBUGgg4MDra6uqlarqdfrSdKI7ezsTJLUbrfDqnKRLN/3JUnr6+uKx+Pa2dnRwsKCCoWCyuWybNvW7u6u0um08vm8JMn3/bBXDYBD13VljNHl5aVSqZQymUwUeH19rcXFRS0vL0uSbm5uImnGGLmuK+AwBiAp6rplWQRBAEAikeDq6grLsjDGRP7b29uR+HByb+5KW1tbUzwel+d5Wlpa0srKikqlkmzb1vb2trLZrHK5nIIg0GAwUCKREOABvAB0fHwclbu/v69isahGo6Hz83MZY7S3txfZLi4uJEndbjds9sto/I7j/Pf45+fnBfSGtwlADVClUlEQBI9aSM/z7i1kiPeAyuXyRLKNjY1w7J/GT2SEbGpqSkdHR+r3+9HR9vt9NZtNOY4TVtKa9AFqwLdhsObm5u5evIa+rfEk6wEyB3gGvB4+OIAfwJfhg/sznvAX84BJ9VztlGoAAAAASUVORK5CYII=';

    const SHARE_18px = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASCAQAAAD4MpbhAAAAAmJLR0QA/4ePzL8AAAD2SURBVCjPbdFNK4RxFAXw3zwzjSZ5+QYzGGpWyqxYWEi+gShlQ8TaQjY2PoW9vGylpKxIoqyUkiikNAsWw4R4bJ5Gev73bk6d073n3pMVqoyyORVP6mF60LdY7Ew5JMjaFSc9GwUEP7qauDOXoosmtCe44SLzj8ybNqVhW8mYd5u2Mhi1qMW+R/NabdjzIKfbh3sxfWpisborqyppSytNx4dKaTry5+LFW+jqos9kQs2CQkhSdeDUjHG3TgwpIK9Dm2z6h2uereu37Nq5Sfn0vF476snSLyPBMF02L1uKgmHdNPFrOO7h5HlHejLBFZGqAQ3H7n4BpPJL0/n4/qAAAAAASUVORK5CYII=';

    const CC_BUTTON_HEAD = '<div class="cc-attribution-element"><button class="cc-attribution-button cc-attribution-copy-button" data-clipboard-action="copy" data-clipboard-text="';

    const CC_BUTTON_TAIL = '"><span data-l10n-id="Share">Share</span>'
                         /*  . self::CC_ICON_18px
                             . '" class="cc-attribution-button-cc"> <img src="'
                             . self::CCButton.prototype.SHARE_18px
                             . '" class="cc-attribution-button-share">*/
                         . '</button>
<div class="cc-dropdown-wrapper"><button class="cc-attribution-format-select">HTML &#x25BC;</button>
<ul class="cc-dropdown-menu" aria-haspopup="true" aria-expanded="false">
    <li class="cc-dropdown-menu-item"><a class="cc-dropdown-menu-item-link cc-dropdown-menu-item-link-selected cc-format-html-rdfa" data-cc-format="html-rdfa" href="#">HTML</a></li>
    <li class="cc-dropdown-menu-item"><a class="cc-dropdown-menu-item-link cc-format-text" data-cc-format="text" href="#">Text</a></li></ul></div>
<button class="cc-attribution-help-button" data-l10n-id="?">?</button></div>';

    static function mediaToText ($media) {
        return preg_replace('/^.+ src="([^"]+)".+$/', '<$1>', $media);
    }

    static function htmlToText ($metadata) {
        $result = preg_replace('|<br>|', ' ', $metadata);
        $result = preg_replace('|<img [^>]+>|', '', $result);
        $result = preg_replace('|</?span[^>]*>|', '', $result);
        // Remove links made empty by previous operations
        $result = preg_replace('|<a[^>]+href[^>]+>\s*</a>|', '', $result);
        // Convert surviving links
        $result = preg_replace('|<a[^>]+href="([^"]+)"[^>]*>([^>]+)</a>|', '$2 <$1>', $result);
        return trim($result);
    }

    public static function markup ($html_rdfa, $text, $button_height, $media) {
        if ($media) {
            $html_rdfa =  $media . '<br>' . $html_rdfa;
        }
        if (! $text) {
            $text = htmlentities(self::mediaToText($media) . ' ' . self::htmlToText($html_rdfa));
        }
        $html_rdfa = htmlentities($html_rdfa);
        $button = self::CC_BUTTON_HEAD
                // Start with the metadata as html. Have JS copy it in?
                . $html_rdfa
                . '" data-cc-attribution-html-rdfa='
                . $html_rdfa
                . '" data-cc-attribution-text="'
                . $text
                . self::CC_BUTTON_TAIL;
        return $button;
    }

    private static function basedir () {
        return plugin_dir_url(dirname(__FILE__));
    }

    public static function cc_1ca_add_theme_scripts () {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui',
                         self::basedir() . 'css/jquery-ui.css');
        wp_enqueue_script('clipboard.js',
                          'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js',
                          [],
                          '1.5.10',
                          true);
        wp_enqueue_style('cc-button',
                         self::basedir() . 'css/cc-button.css',
                         false,
                         '1.1',
                         'all');
        wp_enqueue_script('cc-button-support',
                          self::basedir() . 'js/cc-button-support.js',
                          ['jquery-ui-dialog', 'clipboard.js'],
                          '1.1',
                          true);
    }

    public static function cc_1ca_insert_footer () {
        echo "<script>
      var ccButton = new CCButton();
      ccButton.addEventListeners();
</script>";
    }

    public static function init () {
        add_action('wp_enqueue_scripts',
                   array(get_called_class(), 'cc_1ca_add_theme_scripts'));
        // Low priority so we go after the scripts are included
        add_action('wp_footer',
                   array(get_called_class(), 'cc_1ca_insert_footer'),
                   1000);
    }

};
