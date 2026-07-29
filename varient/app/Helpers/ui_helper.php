<?php

if (!function_exists('formRadio')) {
    /**
     * Generates a styled radio button group
     *
     * @param string $name The 'name' attribute for the radio group.
     * @param array $options Associative array of [value => label].
     * @param mixed $currentValue The value of the currently checked option.
     * @param string $wrapperClass *Additional* CSS class(es) for the main wrapper.
     * @param string $optionClass *Additional* CSS class(es) for each individual option.
     *
     * @return string The generated HTML.
     */
    function formRadio(string $name, array $options, $currentValue = null, string $wrapperClass = '', string $optionClass = '')
    {
        $radioItems = [];

        // Base classes are defined internally
        $baseWrapperClass = 'd-flex align-items-center gap-10';
        $baseOptionClass = 'form-check form-check-custom form-check-solid';

        // Combine base classes with any provided additional classes
        $finalWrapperClass = $baseWrapperClass . ($wrapperClass ? ' ' . $wrapperClass : '');
        $finalOptionClass = $baseOptionClass . ($optionClass ? ' ' . $optionClass : '');

        // Use CodeIgniter's 'esc' function for security
        $safeName = esc($name, 'attr');
        $safeOptionClass = esc($finalOptionClass, 'attr');

        foreach ($options as $value => $label) {
            // Escape values for attributes
            $safeValue = esc($value, 'attr');

            // Generate a unique ID with a safe prefix ('r_')
            $id = 'r_' . uniqid();

            // Standard strict string comparison (works for 'yes'=='yes' and '1'=='1')
            $isChecked = (string)$currentValue === (string)$value;

            // Patch for the 'null' case (treats null as '0' or 0)
            if ($isChecked === false && $currentValue === null && ($value === 0 || $value === '0')) {
                $isChecked = true;
            }

            $checkedAttr = $isChecked ? 'checked' : '';

            // Build the HTML for this radio item
            $radioItems[] = <<<HTML
            <div class="$safeOptionClass">
                <input type="radio" name="$safeName" value="$safeValue" id="$id" class="form-check-input" required $checkedAttr/>
                <label class="form-check-label text-gray-900" for="$id">$label</label>
            </div>
            HTML;
        }

        // Join all radio items
        $allRadiosHtml = implode("\n", $radioItems);

        $safeWrapperClass = esc($finalWrapperClass, 'attr');

        // Place the items into the final wrapper template
        $finalHtml = <<<HTML
        <div class="$safeWrapperClass">
            $allRadiosHtml
        </div>
        HTML;

        return $finalHtml;
    }
}

if (!function_exists('formSwitch')) {
    /**
     * Generates ONLY the core switch component (input and optional label)
     *
     * @param string $name The 'name' attribute for the input.
     * @param mixed $currentValue The current value (e.g., '1', '0', null) to check against.
     * @param string $innerLabel (Optional) The label to display next to the switch.
     * @param string $wrapperClass (Optional) *Additional* CSS classes for the main <div> wrapper.
     * @param string $inputClass (Optional) *Additional* CSS classes for the <input> element.
     *
     * @return string The generated HTML.
     */
    function formSwitch(string $name, $currentValue, string $innerLabel = '', string $wrapperClass = '', string $inputClass = ''): string
    {
        $baseWrapper = 'form-check form-check-solid form-switch form-check-custom form-check-success fv-row';
        $baseInput = 'form-check-input w-40px h-25px';
        $checkedValue = 1;
        $uncheckedValue = 0;

        // Prepare Variables
        $id = 'sw_' . uniqid();
        $safeName = esc($name, 'attr');

        // Checked State
        $isChecked = (string)$currentValue === (string)$checkedValue;
        $checkedAttr = $isChecked ? 'checked' : '';

        // Combine Classes
        $finalWrapperClass = $baseWrapper . ($wrapperClass ? ' ' . $wrapperClass : '');
        $finalInputClass = $baseInput . ($inputClass ? ' ' . $inputClass : '');
        $safeWrapperClass = esc($finalWrapperClass, 'attr');
        $safeInputClass = esc($finalInputClass, 'attr');

        // Optional Label HTML
        $labelHtml = '';
        if (!empty(trim($innerLabel))) {
            $labelHtml = <<<HTML
            <label class="form-check-label" for="$id">$innerLabel</label>
            HTML;
        }

        // Assemble HTML
        $html = <<<HTML
        <div class="$safeWrapperClass">
            <input type="hidden" name="$safeName" value="$uncheckedValue">
            <input 
                type="checkbox" 
                name="$safeName" 
                value="$checkedValue" 
                id="$id" 
                class="$safeInputClass" 
                $checkedAttr
            />
            $labelHtml
        </div>
        HTML;

        return $html;
    }
}

if (!function_exists('formCheckbox')) {
    /**
     * Generates a standard checkbox component (input and optional label)
     *
     * @param string $name The 'name' attribute for the input.
     * @param mixed $currentValue The current value (e.g., '1', '0', null) to check against.
     * @param string $innerLabel (Optional) The label to display next to the checkbox.
     * @param string $wrapperClass (Optional) *Additional* CSS classes for the main <div> wrapper.
     * @param string $inputClass (Optional) *Additional* CSS classes for the <input> element.
     *
     * @return string The generated HTML.
     */
    function formCheckbox(string $name, $currentValue, string $innerLabel = '', string $wrapperClass = '', string $inputClass = ''): string
    {
        $baseWrapper = 'form-check form-check-custom form-check-solid fv-row';
        $baseInput = 'form-check-input';
        $checkedValue = 1;
        $uncheckedValue = 0;

        // Prepare Variables
        $id = 'cb_' . uniqid();
        $safeName = esc($name, 'attr');

        // Checked State
        $isChecked = (string)$currentValue === (string)$checkedValue;
        $checkedAttr = $isChecked ? 'checked' : '';

        // Combine Classes
        $finalWrapperClass = $baseWrapper . ($wrapperClass ? ' ' . $wrapperClass : '');
        $finalInputClass = $baseInput . ($inputClass ? ' ' . $inputClass : '');
        $safeWrapperClass = esc($finalWrapperClass, 'attr');
        $safeInputClass = esc($finalInputClass, 'attr');

        // Optional Label HTML
        $labelHtml = '';
        if (!empty(trim($innerLabel))) {
            $labelHtml = <<<HTML
            <label class="form-check-label text-gray-900" for="$id">
                $innerLabel
            </label>
            HTML;
        }

        // Assemble HTML
        $html = <<<HTML
        <div class="$safeWrapperClass">
            <input type="hidden" name="$safeName" value="$uncheckedValue">
            <input 
                type="checkbox" 
                class="$safeInputClass" 
                name="$safeName" 
                value="$checkedValue" 
                id="$id" 
                $checkedAttr 
            />
            $labelHtml
        </div>
        HTML;

        return $html;
    }
}

if (!function_exists('renderAlert')) {
    /**
     * Generates a styled alert box
     *
     * @param string $type Theme color ('primary', 'success', 'danger', etc.).
     * @param string $icon Icon shape ('danger' or 'default'/'notification').
     * @param string $title The main title (h4) of the alert.
     * @param string|array $description The body text. Array items become new lines.
     * @param string $wrapperClass (Optional) Additional CSS classes for the main wrapper.
     *
     * @return string The generated HTML.
     */
    function renderAlert(string $type, string $icon, string $title, $description, string $wrapperClass = '', bool $withMarginTop = true): string
    {
        $safeType = esc($type, 'attr');
        $colorClass_bg = "bg-light-{$safeType}";
        $colorClass_border = "border";
        $colorClass_text = "text-{$safeType}";

        $mtClass = $withMarginTop ? 'mt-7' : '';

        // Set icon HTML based on $icon
        $iconHtml = '';
        if ($icon === 'danger') {
            $iconHtml = <<<HTML
            <i class="ki-duotone ki-information fs-2tx $colorClass_text me-4 mb-5 mb-sm-0">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            HTML;
        } else {
            $iconHtml = <<<HTML
            <i class="ki-duotone ki-notification-bing fs-2hx $colorClass_text me-4 mb-5 mb-sm-0">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            HTML;
        }

        // Build the main wrapper class
        $baseWrapper = 'alert alert-dismissible d-inline-flex flex-column flex-sm-row p-5 mb-3';
        $finalWrapperClass = trim($baseWrapper . ' ' . $mtClass . ' ' . $colorClass_bg . ' ' . $colorClass_border);
        $finalWrapperClass .= ($wrapperClass ? ' ' . $wrapperClass : '');
        $safeWrapperClass = esc($finalWrapperClass, 'attr');

        // Build description (handles array for multi-line)
        $descHtml = '';
        if (is_array($description)) {
            foreach ($description as $line) {
                $descHtml .= "<span class=\"mb-1\">{$line}</span>";
            }
        } else {
            $descHtml = "<span class=\"mb-1\">{$description}</span>";
        }

        // Assemble the final HTML
        $html = <<<HTML
        <div class="$safeWrapperClass">
            $iconHtml
            <div class="d-inline-flex flex-column pe-0 pe-sm-10">
                <h4 class="fw-semibold $colorClass_text">$title</h4>
                $descHtml
            </div>
        </div>
        HTML;

        return $html;
    }
}