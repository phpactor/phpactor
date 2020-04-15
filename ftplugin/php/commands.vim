""
" Extract a new method from the current selection
command! -buffer -range=% PhpactorExtractMethod call phpactor#ExtractMethod()

""
" Extract the selected expression and assign it to a variable before (placing
" it before the current statement)
command! -buffer -range=% PhpactorExtractExpression call phpactor#ExtractExpression('v')

""
" Extract a constant from a literal
command! -buffer -nargs=0 PhpactorExtractConstant call phpactor#ExtractConstant()

""
" Expand the class name under the cursor to it's fully-qualified-name
command! -buffer -nargs=0 PhpactorClassExpand call phpactor#ClassExpand()

""
" Import the name under the cusor. If multiple options are available, you
" are able to choose one.
command! -buffer -nargs=0 PhpactorImportClass call phpactor#ImportClass()

""
" Attempt to import all non-resolvable classes in the current class (based
" on offset position)
command! -buffer -nargs=0 PhpactorImportMissingClasses call phpactor#ImportMissingClasses()

""
" Goto the definition of the class, method or function under the cursor. Open
" the definition in the current window.
command! -buffer -nargs=0 PhpactorGotoDefinition call phpactor#GotoDefinition()

""
" Goto type (class) of the symbol under the cursor.
command! -buffer -nargs=0 PhpactorGotoType call phpactor#GotoType()

""
" As with @command(PhpactorGotoDefinition) but open in a vertical split.
command! -buffer -nargs=0 PhpactorGotoDefinitionVsplit call phpactor#GotoDefinitionVsplit()

""
" As with @command(PhpactorGotoDefinition) but open in a horizontal split.
command! -buffer -nargs=0 PhpactorGotoDefinitionHsplit call phpactor#GotoDefinitionHsplit()

""
" As with @command(PhpactorGotoDefinition) but open in a new tab
command! -buffer -nargs=0 PhpactorGotoDefinitionTab call phpactor#GotoDefinitionTab()

""
" Load all implementations of the class under the cursor into the quick-fix
" list.
command! -buffer -nargs=0 PhpactorGotoImplementations call phpactor#GotoImplementations()

""
" Show information about the symbol under the cursor.
command! -buffer -nargs=0 PhpactorHover call phpactor#Hover()

""
" Show the context menu for the current cursor position.
command! -buffer -nargs=0 PhpactorContextMenu call phpactor#ContextMenu()

""
" Copy the current file - updating the namespace and class name according to
" the new file location and name
command! -buffer -nargs=0 PhpactorCopyFile call phpactor#CopyFile()

""
" Move the current file - updating the namespace and class name according to
" the new file location and name
command! -buffer -nargs=0 PhpactorMoveFile call phpactor#MoveFile()

""
" Create a new class. You will be offered a choice of templates.
command! -buffer -nargs=0 PhpactorClassNew call phpactor#ClassNew()

""
" Inflect a new class from the current class (e.g. generate an interface for
" the current class)
command! -buffer -nargs=0 PhpactorClassInflect call phpactor#ClassInflect()

""
" Attempt to find all references to the class name or method under the cursor.
" The results will be loaded into the quik-fix list
command! -buffer -nargs=0 PhpactorFindReferences call phpactor#FindReferences()

""
" Navigate - jump to the parent class, interface, or any of the relationships
" defined in `navigation.destinations` https://phpactor.github.io/phpactor/configuration.html#reference
command! -buffer -nargs=0 PhpactorNavigate call phpactor#Navigate()

""
" Rotate the visiblity of the method under the cursor
command! -buffer -nargs=0 PhpactorChangeVisibility call phpactor#ChangeVisibility()

""
" Generate accessors for the current class
command! -buffer -nargs=0 PhpactorGenerateAccessors call phpactor#GenerateAccessors()

""
" Automatically add any missing properties to a class
command! -buffer -nargs=0 PhpactorTransform call phpactor#Transform()
