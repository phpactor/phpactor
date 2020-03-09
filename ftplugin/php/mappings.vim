""
" @section Mappings
"
" Phpactor does not assume any mappings automatically, the following mappings
" are available for you to copy: >
"
"   augroup PhpactorMappings
"     au!
"     au FileType php nmap <buffer> <Leader>u :PhpactorImportClass<CR>
"     au FileType php nmap <buffer> <Leader>ua :PhpactorImportMissingClasses<CR>
"     au FileType php nmap <buffer> <Leader>mm :PhpactorContextMenu<CR>
"     au FileType php nmap <buffer> <Leader>nn :PhpactorNavigate<CR>
"     au FileType php nmap <buffer> <Leader>oo :PhpactorGotoDefinition<CR>
"     au FileType php nmap <buffer> <Leader>oh :PhpactorGotoDefinitionHsplit<CR>
"     au FileType php nmap <buffer> <Leader>ov :PhpactorGotoDefinitionVsplit<CR>
"     au FileType php nmap <buffer> <Leader>ot :PhpactorGotoDefinitionTab<CR>
"     au FileType php nmap <buffer> <Leader>K :PhpactorHover<CR>
"     au FileType php nmap <buffer> <Leader>tt :PhpactorTransform<CR>
"     au FileType php nmap <buffer> <Leader>cc :PhpactorClassNew<CR>
"     au FileType php nmap <buffer> <silent> <Leader>ee :PhpactorExtractExpression<CR>
"     au FileType php vmap <buffer> <silent> <Leader>ee :<C-u>PhpactorExtractExpression<CR>
"     au FileType php vmap <buffer> <silent> <Leader>em :<C-u>PhpactorExtractMethod<CR>
"   augroup END
" <
"
