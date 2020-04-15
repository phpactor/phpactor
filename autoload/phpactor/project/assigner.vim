function! phpactor#project#assigner#assignProjectToBuffer(filename)
  if !exists('g:phpactorLoaded')
    return
  endif

  if exists('b:project')
    return
  endif
  let l:project = g:phpactorBufferMatcher.resolveProjectForFile(a:filename)
  if v:null is l:project
    return
  endif
  let b:project = l:project
  if g:phpactorBufferMatcher.repository.hasProject(b:project)
    return
  endif

  call g:phpactorBufferMatcher.repository.addProject(b:project)
endfunction

function! phpactor#project#assigner#create(projectRepository, projectRootMarkers, filesystemRootMarkers, initialCwd) abort
  let l:filesystemRootMarkers = a:filesystemRootMarkers
  if index(l:filesystemRootMarkers, '/') < 0
    call add(l:filesystemRootMarkers, '/')
  endif

  return {
        \ 'initialCwd': a:initialCwd,
        \ 'projectRootMarkers': a:projectRootMarkers,
        \ 'filesystemRootMarkers': l:filesystemRootMarkers,
        \ 'repository': a:projectRepository,
        \ 'resolveProjectForFile': function('s:resolveProjectForFile')
        \ }
endfunction

function s:resolveProjectForFile(file) dict abort
  let l:project = self.repository.findProjectContainingFile(a:file)

  if type(l:project) == type({})
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

  let l:choices = [
        \ {
        \ 'action': { -> self.initialCwd },
        \ 'message': l:message
        \ },
        \ {
        \ 'action': function('input', ['Enter file path: ', l:initialDirectory, 'file']),
        \ 'message': printf('manual (default "%s")', l:initialDirectory)
        \ }
        \ ]

  if v:null != l:rootDirByMarker
    let l:item = {
          \ 'action': { -> l:rootDirByMarker },
          \ 'message': 'autodetected by root marker: '.l:rootDirByMarker
          \ }
    call add(l:choices, l:item)
  endif

  let l:choice = v:null
  while index(range(0, len(l:choices)-1), l:choice) < 0
    let l:choice = inputlist(map(copy(l:choices), { number, item -> (number ? printf('%d: ', number) : '') . item['message'] }))
    redraw
  endwhile

  let l:selectedDir = l:choices[l:choice]['action']()
  redraw

  if v:null != l:selectedDir
    let l:project = phpactor#project#project#createFromRootPath(l:selectedDir)
    echomsg printf('Project with root "%s" has been created.', l:project.primaryRootPath)

    return l:project
  endif
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
