let g:phpactorProjects = get(g:, 'phpactorProjects', {})

function phpactor#project#projectRepository#findProjectForFile(file) abort
    for l:rootPath in keys(g:phpactorProjects)
      let l:project = g:phpactorProjects[l:rootPath]
      if l:project.containsFile(a:file)
        return l:project
      endif
    endfor

    return v:null
endfunction
