function! phpactor#project#bufferMatcher#create(projectRepository, projectRootMarkers, filesystemRootMarkers, initialCwd) abort
  let l:filesystemRootMarkers = a:filesystemRootMarkers
  if index(l:filesystemRootMarkers, '/') < 0
    call add(l:filesystemRootMarkers, '/')
  endif

  return {
        \ 'initialCwd': a:initialCwd,
        \ 'projectRootMarkers': a:projectRootMarkers,
        \ 'filesystemRootMarkers': l:filesystemRootMarkers,
        \ 'repository': a:projectRepository,
        \ 'getRepository': function('s:getRepository'),
        \ 'matchFileToProject': function('s:matchFileToProject')
        \ }
endfunction

function s:matchFileToProject(file) dict abort
  let l:project = self.repository.findProjectContainingFile(a:file)

  if type(l:project) == v:t_dict
    return l:project
  endif

  " early version to refactor or change
  " heredoc are available since vim 8.1.1354
  let l:message = printf(join([
        \ 'Phpactor [RPC]',
        \ 'There is no project enabled for "%s" yet.',
        \ 'Select the way of assign the project root',
        \ 'If cancelled, "%s" will be used.'
        \], "\n"), a:file, self.initialCwd)

  " see phpactor#_path
  let l:initialDirectory = fnamemodify(a:file, ':p:h')
  let l:rootDirByMarker = s:searchDirectoryUpwardForRootMarkers(
        \ l:initialDirectory,
        \ self.projectRootMarkers,
        \ self.filesystemRootMarkers
        \ )

  let l:choices  = [
          \ l:message,
          \ printf('1. manual (default "%s")', l:initialDirectory),
          \ printf('2. use initial working directory: "%s"', self.initialCwd)
          \ ]

  if v:null != l:rootDirByMarker
    let l:item = '3. autodetected by root marker: '.l:rootDirByMarker
    call add(l:choices, l:item)
  endif

  let l:choice = v:null
  while index([0,1,2,3], l:choice) < 0
    " bug: neovim assigns 0 without any interaction, so I used input()
    let l:choice = inputlist(l:choices)
    " let l:choice = str2nr(input(l:choices[0], join(l:choices[1:], "\n")))
    " echo join(l:choices)."\n"
    " let l:choice = str2nr(input('Enter number: '))
    redraw
  endwhile

  if l:choice == 1
    let l:manualPath = input('Enter file path: ', l:initialDirectory, 'file')
    redraw
    if ! empty(glob(l:manualPath.'/'))
      let l:selectedDir = l:manualPath
    endif
  elseif l:choice == 3
    let l:selectedDir = l:rootDirByMarker
  else
    let l:selectedDir = self.initialCwd
  endif

  if v:null != l:selectedDir
    let l:project = phpactor#project#project#createFromRootPath(l:selectedDir)
    echomsg printf('Project with root "%s" has been created.', l:project.getPrimaryRootPath())

    return l:project
  endif
endfunction

function s:getRepository() dict abort
  return self.repository
endfunction

function! s:searchDirectoryUpwardForRootMarkers(initialDirectory, workspaceRootMarkers, filesystemRootMarkers)
  let l:directory = a:initialDirectory

  while index(a:filesystemRootMarkers, l:directory) < 0
    if s:directoryMatchesToPatterns(l:directory, a:workspaceRootMarkers)
      return l:directory
    endif

    let l:directory = fnamemodify(l:directory, ':h')
  endwhile

  return v:null
endfunction

function s:directoryMatchesToPatterns(directory, patterns) abort
  for l:pattern in a:patterns
    if (filereadable(a:directory .'/'. l:pattern))
      return v:true
    endif
  endfor

  return v:false
endfunction
