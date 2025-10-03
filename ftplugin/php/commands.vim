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
" Import the name under the cursor. If multiple options are available, you
" are able to choose one.
command! -buffer -nargs=0 PhpactorImportClass call phpactor#ImportClass()

""
" Attempt to import all non-resolvable classes in the current class (based
" on offset position)
command! -buffer -nargs=0 PhpactorImportMissingClasses call phpactor#ImportMissingClasses()

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
" Copy the current class FQN (based on current filename) to the clipboard
command! -buffer -nargs=0 PhpactorCopyClassName call phpactor#CopyFullClassName()

""
" Move the current file - updating the namespace and class name according to
" the new file location and name
command! -buffer -nargs=0 PhpactorMoveFile call phpactor#MoveFile()

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
" Generate mutators for the current class
command! -buffer -nargs=0 PhpactorGenerateMutators call phpactor#GenerateMutators()

""
" Automatically add any missing properties to a class
command! -buffer -nargs=0 PhpactorTransform call phpactor#Transform()

""
" Trust configuration in the current working directory
command! -buffer -nargs=0 PhpactorTrust call phpactor#Trust()

" Revoke trust in the current working directory
command! -buffer -nargs=0 PhpactorUntrust call phpactor#Untrust()
