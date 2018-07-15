<?php

namespace Phpactor\Extension\LanguageServer\Response;

class CompletionItem
{
	/**
	 * The label of this completion item. By default
	 * also the text that is inserted when selecting
     * this completion.
     *
     * @var string
	 */
	public $label;

	/**
	 * The kind of this completion item. Based of the kind
	 * an icon is chosen by the editor.
     *
     * @var integer|null
	 */
	public $kind;

	/**
	 * A human-readable string with additional information
	 * about this item, like type or symbol information.
     *
     * @var string|null
	 */
	public $detail;

    /**
     * @var string|MarkupContent|null
     */
	public $documentation;

	/**
	 * Indicates if this item is deprecated.
     *
     * @var bool
	 */
	public $deprecated = false;

	/**
	 * Select this item when showing.
	 *
	 * *Note* that only one completion item can be selected and that the
	 * tool / client decides which item that is. The rule is that the *first*
	 * item of those that match best is selected.
     *
     * @var bool|null
	 */
	public $preselect = false;

	/**
	 * A string that should be used when comparing this item
	 * with other items. When `falsy` the label is used.
     *
     * @var string|null
	 */
	public $sortText;

	/**
	 * A string that should be used when filtering a set of
	 * completion items. When `falsy` the label is used.
     *
     * @var string|null
	 */
	public $filterText;

	/**
	 * A string that should be inserted into a document when selecting
	 * this completion. When `falsy` the label is used.
	 *
	 * The `insertText` is subject to interpretation by the client side.
	 * Some tools might not take the string literally. For example
	 * VS Code when code complete is requested in this example `con<cursor position>`
	 * and a completion item with an `insertText` of `console` is provided it
	 * will only insert `sole`. Therefore it is recommended to use `textEdit` instead
	 * since it avoids additional client side interpretation.
	 *
	 * @deprecated Use textEdit instead.
     * @var string|null
	 */
	public $insertText;

	/**
	 * The format of the insert text. The format applies to both the `insertText` property
	 * and the `newText` property of a provided `textEdit`.
     *
     * @var InsertTextFormat|null
	 */
	public $insertTextFormat;

	/**
	 * An edit which is applied to a document when selecting this completion. When an edit is provided the value of
	 * `insertText` is ignored.
	 *
	 * *Note:* The range of the edit must be a single line range and it must contain the position at which completion
	 * has been requested.
     *
     * @var TextEdit|null
	 */
	public $textEdit;

	/**
	 * An optional array of additional text edits that are applied when
	 * selecting this completion. Edits must not overlap (including the same insert position)
	 * with the main edit nor with themselves.
	 *
	 * Additional text edits should be used to change text unrelated to the current cursor position
	 * (for example adding an import statement at the top of the file if the completion item will
	 * insert an unqualified type).
     *
     * @var TextEdit[]
	 */
	public $additionalTextEdits = [];

    /** 
     * An optional set of characters that when pressed while this completion
     * is active will accept it first and then type that character. *Note* that
     * all commit characters should have `length=1` and that superfluous
     * characters will be ignored.
     *
     * @var string[]
	 */
	public $commitCharacters = [];

	/**
	 * An optional command that is executed *after* inserting this completion. *Note* that
	 * additional modifications to the current document should be described with the
	 * additionalTextEdits-property.
     *
     * @var Command|null
	 */
	public $command;

	/**
	 * An data entry field that is preserved on a completion item between
	 * a completion and a completion resolve request.
     *
     * @var mixed
	 */
	public $data;
}

