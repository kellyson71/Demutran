<?php
function createEditableField($label, $value, $fieldName)
{
    // Trata valor nulo ou vazio
    $value = $value ?? '';
    $fieldId = 'field_' . preg_replace('/[^a-zA-Z0-9]/', '_', $fieldName);
    return "
    <div class='editable-field bg-white p-3 rounded-lg shadow-sm hover:shadow transition-shadow'>
        <div class='flex items-center justify-between w-full'>
            <label class='text-gray-600 font-semibold'>{$label}:</label>
            <div class='flex items-center gap-2 flex-1 ml-3'>
                <input type='text'
                       id='{$fieldId}'
                       name='{$fieldName}'
                       value='" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "'
                       class='field-input flex-1 px-2 py-1'
                       readonly>
                <button onclick='toggleEdit(\"{$fieldId}\")' 
                        class='edit-button text-blue-600 hover:text-blue-800'>
                    <i class='material-icons text-sm'>edit</i>
                </button>
                <button onclick='saveField(\"{$fieldId}\")' 
                        class='save-button hidden text-green-600 hover:text-green-800'>
                    <i class='material-icons text-sm'>check</i>
                </button>
                <button onclick='cancelEdit(\"{$fieldId}\")' 
                        class='cancel-button hidden text-red-600 hover:text-red-800'>
                    <i class='material-icons text-sm'>close</i>
                </button>
            </div>
        </div>

    </div>";
}
