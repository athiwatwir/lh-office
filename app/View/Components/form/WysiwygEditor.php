<?php

namespace App\View\Components\form;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class WysiwygEditor extends Component
{
    public string $editorId;

    public function __construct(
        public string $name,
        public string $id = '',
        public ?string $label = null,
        public ?string $value = null,
        public ?string $uploadUrl = null,
        public int $height = 360,
        public bool $required = false,
        public string $placeholder = 'พิมพ์รายละเอียด...',
        public bool $enableYoutube = false,
    ) {
        if ($this->id === '') {
            $this->id = $this->name;
        }

        $this->editorId = 'hs-editor-'.Str::slug($this->id, '-');
        $this->uploadUrl ??= route('editor.upload-image');
    }

    public function render(): View
    {
        return view('components.form.wysiwyg-editor');
    }
}
