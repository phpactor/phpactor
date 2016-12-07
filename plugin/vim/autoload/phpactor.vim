let g:phpactor#phpactor_path="/home/daniel/www/dantleech/phpactor/bin/phpactor"

func! phpactor#complete(findstart, base)

    if a:findstart
        let line = getline('.')
        let start = col('.') - 1

        while start > 0 && (line[start - 1] =~ '\a' || line[start - 1] == '$')
            let start -= 1
        endwhile

        return start
    else
        let offset = line2byte(line(".")) + col(".")
        let command = g:phpactor#phpactor_path . ' complete ' . string(offset)

        " TODO: We want to pass the entire source to the autocompleter, but
        "       the source retrieved above does not include the partial word
        "       (a:base) so we "insert" it here, but very clumsily.
        let stdin = join(getline(1,'.'), "\n")
        let stdin = stdin . a:base
        let stdin = stdin . "\n" . join(getline(line('.') + 1, '$'), "\n")
        let out = system(command, stdin)

        let suggestions = json_decode(out)

        if empty(suggestions)
            echom "Could not decode response (offset " . offset . "): " . out
            return
        endif

        let completions = []

        for suggestion in suggestions
            call add(completions, { 'word': suggestion['name'], 'info': suggestion['info'], 'kind': suggestion['type']})
        endfor

        return completions
    endif

endfunc
