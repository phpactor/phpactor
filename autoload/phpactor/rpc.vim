func! phpactor#rpc#handleRawResponse(response)
    if "" == a:response
        return v:null
    endif

    try 
        let response = json_decode(a:response)
    catch 
        throw "Could not parse response from Phpactor: " . v:exception
    endtry

    let actionName = response['action']
    let parameters = response['parameters']

    return phpactor#_rpc_dispatch(actionName, parameters)
endfunc
