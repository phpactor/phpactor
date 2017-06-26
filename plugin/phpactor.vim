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

function! PhactUseAdd()
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
        let originalCmdHeight = &cmdheight
        let &cmdheight = height
        for info in results
            echo c . ": " . info['class']
            let c = c + 1
        endfor

        let choice = input('Choose: ')
        let &cmdheight = originalCmdHeight
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

    if (namespaceLineNb == 0)
        let namespaceLineNb = 3
    endif

    call cursor(1, 1)
    let lastUseLineNb = namespaceLineNb

    let result = -1
    while (result != 0)
        let result = search('^use', '', line("w$"))

        if (result > 0)
            let lastUseLineNb = result
        endif
    endwhile

    call cursor(lastUseLineNb, 1)
    let line = getline(line('.') + 1)
    if (!empty(line))
        exec "normal! O"
    endif

    exec "normal! ouse " . classInfo['class'] . ";"

    let line = getline(line('.') + 1)
    if (!empty(line))
        exec "normal! o"
    endif

    " END: Insert use statement
endfunction

function! PhactExec(cmd)
    let result = system('php ' . s:genpath . ' ' . a:cmd)

    if (v:shell_error == 0)
        return result
    else 
        echoerr result
    endif
endfunction

function! PhactNamespaceInsert()
    exec "normal! i" . PhactNamespaceGet()
endfunction

