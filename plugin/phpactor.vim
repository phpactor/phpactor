" phpnamespace - Composer namepspace generator
"
" Author: Daniel Leech <daniel@dantleech.com>

let s:genpath = expand('<sfile>:p:h') . '/../bin/phpactor'

function! PhactNamespaceGet()
    let currentPath = expand('%')
    let command = 'file:info --format=json ' . currentPath
    let out = PhactExec(command)
    let results = json_decode(out)

    return results['class_namespace']
endfunction

""""""""""""""""""""""""
" Autocomplete
""""""""""""""""""""""""
function! PhactComplete(findstart, base)

    if a:findstart
        return 0
    endif

    let matched = matchstr(a:base, "->")

    if (matched == "->")
        let offset = line2byte(line(".")) + col(".") + strlen(a:base) - 4
        let stdin = join(getline(1,'.'), "\n")
        let stdin = stdin . a:base
        let stdin = stdin . "\n" . join(getline(line('.') + 1, '$'), "\n")

        let command = 'file:offset --format=json stdin ' . offset
        let results = PhactExecStdIn(command, stdin)
        let results = json_decode(results)

        if (results['type'] == "<unknown>")
            echo "Type could not be determined"
            return -2
        endif

        let command = 'class:reflect --format=json ' . results['path']
        let reflection = PhactExec(command)
        let reflection = json_decode(reflection)

        let completions = []

        if !empty(reflection['methods'])
            for method in values(reflection['methods'])
                call add(completions, { 'word': a:base . method['name'], 'info': '', 'kind': 'Method'})
            endfor
        endif

        if !empty(reflection['properties'])
            for property in values(reflection['properties'])
                call add(completions, { 'word': a:base . property['name'], 'info': '', 'kind': 'Prop'})
            endfor
        endif

        return completions
    endif

    return -2
endfunc

""""""""""""""""""""""""
" Insert a use statement
""""""""""""""""""""""""
function! PhactUseAdd()
    let savePos = getpos(".")

    " START: Resolve FQN for class
    let word = expand("<cword>")

    let command = 'class:search --format=json ' . word
    let out = PhactExec(command)
    let results = json_decode(out)

    if (len(results) == 0)
        echo "Could not find class"
        echo results
        return
    endif

    if (len(results) > 1)
        let c = 1
        let height = len(results) + 1
        let list = []
        for info in results
            let list = add(list, c . '. ' . info['class'])
            let c = c + 1
        endfor

        let choice = inputlist(list)
        if (choice == 0)
            return
        endif
        let choice = choice - 1

        let classInfo = get(results, choice, {})

        if ({} == classInfo)
            echo "Invalid choice"
            return
        endif
    endif

    if (len(results) == 1)
        let classInfo = results[0]
    endif

    call cursor(1, 1)
    let existing = search('^.*use.*\\' . classInfo['class_name'] . ';$')

    if (existing > 0)
        echo "\n"
        echo "Use statement already included on line:" . existing
        return
    endif
    "END: Resolve FQN for class

    " START: Insert use statement
    call cursor(1, 1)
    let namespaceLineNb = search('^namespace') + 1

    " Find an appropriate place to put the use statement,
    " if there is no namespace, put it after the start tag
    if (namespaceLineNb == 0)
        let namespaceLineNb = 2
    endif

    " Search for the last use statement
    call cursor(1, 1)
    let lastUseLineNb = namespaceLineNb
    let result = -1
    while (result != 0)
        let result = search('^use', '', line("w$"))

        if (result > 0)
            let lastUseLineNb = result
        endif
    endwhile

    " Try and put the cursor at the best place
    call cursor(lastUseLineNb, 1)

    " Ensure an empty line before the use statement
    let extraLines = 1
    let line = getline(line('.') + 1)
    if (!empty(line))
        exec "normal! O"
        let extraLines += 1
    endif

    " Insert use statement
    execute "normal! ouse " . classInfo['class'] . ";"

    " Ensure an empty line afterwards
    let line = getline(line('.') + 1)
    if (!empty(line))
        exec "normal! o"
        let extraLines += 1
    endif

    " Retore the cursor position
    let savePos = [savePos[0], savePos[1] + extraLines, savePos[2], savePos[3]]
    " END: Insert use statement
    call setpos('.', savePos)
endfunction

""""""""""""""""
" Goto defintion
""""""""""""""""
function! PhactGotoDefinition()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let currentPath = expand('%')

    let command = 'file:offset --format=json ' . currentPath . ' ' . offset
    let out = PhactExec(command)
    let results = json_decode(out)

    if (empty(results['path']))
        echo "Could not locate class at offset: " . offset
        return
    endif

    exec "edit " . results['path']

endfunction

function! PhactReflectAtOffset()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")

    let command = 'file:offset --format=json stdin ' . offset
    let out = PhactExecStdIn(command, stdin)
    let results = json_decode(out)

    if (empty(results['path']))
        echo "Could not locate class at offset: " . offset
        return
    endif

    let command = 'class:reflect ' . results['path']
    let out = PhactExec(command)
    echo out

endfunction

function! PhactCopyFile()
    let currentPath = expand('%')
    let destPath = input("Copy to: ", currentPath, "file")
    let command = 'class:copy ' . currentPath . ' ' . destPath
    let out = PhactExec(command)
    echo out
    exec "edit " . destPath
endfunction

function! PhactMoveFile()
    let currentPath = expand('%')
    let destPath = input("Move to: ", currentPath, "file")
    let command = 'class:move ' . currentPath . ' ' . destPath
    echo "\nWARNING: This command will move the class and update ALL references in the git tree."
    echo "         It is not guranteed to succeed. COMMIT YOUR WORK FIRST!"
    echo "NOTE: Currently buffers will not be reloaded"
    let confirm =  confirm('Do you want to proceed?', "&Yes\n&No")

    if confirm == 2
        echo "Cancelled"
        return
    endif

    let out = PhactExec(command)
    echo out
    exec "edit " . destPath
endfunction

function! PhactOffsetTypeInfo()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")

    let command = 'file:offset --frame stdin ' . offset
    let out = PhactExecStdIn(command, stdin)

    echo out

endfunction

function! PhactExec(cmd)
    return PhactExecStdIn(a:cmd, '')
endfunction

function! PhactExecStdIn(cmd, stdin)
    let result = system('php ' . s:genpath . ' --verbose ' . a:cmd, a:stdin)

    if (v:shell_error == 0)
        return result
    else 
        throw result
    endif
endfunction

function! PhactNamespaceInsert()
    exec "normal! i" . PhactNamespaceGet()
endfunction

