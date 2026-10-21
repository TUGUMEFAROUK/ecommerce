<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AdminCategoryRequest extends FormRequest
{
    /**
     * Slugs that would shadow a static admin route.
     *
     * The admin category routes register `categories/create` alongside
     * `categories/{category}`, so a category whose slug is "create" could
     * never be opened from the admin panel: the static route always wins.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = ['create'];

    /**
     * Determine if the user is authorized to make this request.
     *
     * Admin-only form request: the route middleware enforces this too, but
     * the request itself never trusts a missing middleware.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $parentRule = Rule::exists('categories', 'id');

        if ($this->route('category') instanceof Category) {
            $parentRule = $parentRule->whereNot('id', $this->route('category')->getKey());
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(self::RESERVED_SLUGS),
                Rule::unique('categories', 'slug')->ignore($this->route('category')),
            ],
            'parent_id' => ['nullable', 'integer', $parentRule],
        ];
    }

    /**
     * Cross-field validation: a category may never become its own ancestor.
     *
     * The direct self-parent case is already blocked by the exists rule
     * above; this hook walks the proposed parent's chain to catch indirect
     * cycles such as A → B → A, which would otherwise make the tree
     * unresolvable.
     *
     * @return list<callable(\Illuminate\Contracts\Validation\Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $category = $this->route('category');
                $parentId = $this->input('parent_id');

                if (! $category instanceof Category || $parentId === null) {
                    return;
                }

                $parentId = (int) $parentId;
                $visited = [$category->getKey()];
                $current = $parentId;

                while ($current !== null && ! in_array($current, $visited, true)) {
                    $visited[] = $current;
                    $current = Category::query()->whereKey($current)->value('parent_id');
                    $current = $current === null ? null : (int) $current;
                }

                if ($current !== null && in_array($current, $visited, true)) {
                    $validator->errors()->add(
                        'parent_id',
                        'A category cannot be placed under one of its own subcategories.',
                    );
                }
            },
        ];
    }
}
