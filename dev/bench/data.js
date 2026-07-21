window.BENCHMARK_DATA = {
  "lastUpdate": 1784672567663,
  "repoUrl": "https://github.com/phpactor/phpactor",
  "entries": {
    "Phpactor Benchmarks": [
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "e55467cb0a9c40e47df39051ab7b8dd34dc6ae17",
          "message": "Do not use \"auto\" time unit",
          "timestamp": "2026-03-21T18:28:34Z",
          "tree_id": "ff700205cba0cdb57620af07493504f2f68ee723",
          "url": "https://github.com/phpactor/phpactor/commit/e55467cb0a9c40e47df39051ab7b8dd34dc6ae17"
        },
        "date": 1774117829034,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.24696477495105,
            "range": "± 2.68%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 164.27268884539728,
            "range": "± 0.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.3111193737768865,
            "range": "± 0.93%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.724397260273932,
            "range": "± 0.56%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.03316234833659558,
            "range": "± 1.67%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03457581213307178,
            "range": "± 1.31%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05687369863013621,
            "range": "± 1.10%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.01967338551859104,
            "range": "± 6.54%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.09325831702543969,
            "range": "± 1.00%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05740735812133071,
            "range": "± 9.11%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.17440430528376,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 557,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1335,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.30799412915857,
            "range": "± 0.84%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.474461839530354,
            "range": "± 4.02%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09189119373776895,
            "range": "± 2.38%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09140371819960985,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.0902465753424652,
            "range": "± 1.56%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09120665362035171,
            "range": "± 1.71%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09108493150684895,
            "range": "± 5.12%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.08842270058708455,
            "range": "± 1.62%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.09047788649706354,
            "range": "± 3.07%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6785103718199648,
            "range": "± 3.69%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.059771624266144026,
            "range": "± 4.40%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1395929549902152,
            "range": "± 6.35%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.14095107632093926,
            "range": "± 11.54%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13458317025440306,
            "range": "± 5.74%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1356673189823874,
            "range": "± 7.41%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1127323,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08983757338551857,
            "range": "± 13.15%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 344,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 308,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 291,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 77211.0782778865,
            "range": "± 176.48%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 314905.8082191789,
            "range": "± 0.25%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 71474.36007827798,
            "range": "± 0.77%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28731.281800390836,
            "range": "± 0.52%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25043.837573385637,
            "range": "± 0.35%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30168.066536203092,
            "range": "± 0.41%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 818233.3933463655,
            "range": "± 0.48%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 117079,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.6322485322896445,
            "range": "± 1.02%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.093571428571465,
            "range": "± 0.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17170.28180039191,
            "range": "± 0.40%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 151.1743013698638,
            "range": "± 0.31%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 144.85020352250413,
            "range": "± 0.55%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.7440410958904213,
            "range": "± 1.02%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.114698630136989,
            "range": "± 3.72%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.228949119373781,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9698923679060899,
            "range": "± 1.08%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4210726027397222,
            "range": "± 0.56%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.78,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 97.21092465753557,
            "range": "± 0.45%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 103.44733365949232,
            "range": "± 0.84%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 170094,
            "range": "± 194.95%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 117093.4794520555,
            "range": "± 0.86%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "e55467cb0a9c40e47df39051ab7b8dd34dc6ae17",
          "message": "Do not use \"auto\" time unit",
          "timestamp": "2026-03-21T18:28:34Z",
          "tree_id": "ff700205cba0cdb57620af07493504f2f68ee723",
          "url": "https://github.com/phpactor/phpactor/commit/e55467cb0a9c40e47df39051ab7b8dd34dc6ae17"
        },
        "date": 1774118058016,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.502767123287837,
            "range": "± 1.54%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 166.3120489236794,
            "range": "± 0.85%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.4262446183952635,
            "range": "± 1.99%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.789095890411232,
            "range": "± 0.94%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.03311502935420781,
            "range": "± 1.68%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03461295499021525,
            "range": "± 1.71%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05718058708414852,
            "range": "± 1.72%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.019633894324853268,
            "range": "± 5.23%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.09515561643835678,
            "range": "± 1.47%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05828387475538144,
            "range": "± 3.57%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.708176125244634,
            "range": "± 7.46%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 696,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1384,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.546602739725916,
            "range": "± 1.41%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.97086301369847,
            "range": "± 1.40%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09316027397260257,
            "range": "± 4.45%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09217436399217184,
            "range": "± 3.20%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.0925978473581215,
            "range": "± 3.92%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09245616438356105,
            "range": "± 1.66%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09196712328767088,
            "range": "± 2.92%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.09176986301369928,
            "range": "± 1.35%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.09084324853229069,
            "range": "± 1.64%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6995596868884482,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05519804305283749,
            "range": "± 3.30%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.14638943248532274,
            "range": "± 6.01%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.14667906066536193,
            "range": "± 10.63%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13968493150684919,
            "range": "± 7.43%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13923091976516622,
            "range": "± 9.67%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1210551,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.0913131115459882,
            "range": "± 13.13%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 311,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 300,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 310,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 79621.98043052838,
            "range": "± 176.48%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 320762.6731898254,
            "range": "± 1.24%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 72635.78473581202,
            "range": "± 0.81%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 29265.26418786697,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25321.281800391207,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30741.24657534244,
            "range": "± 1.58%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 826899.4794520579,
            "range": "± 1.21%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 124099,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.6655616438355918,
            "range": "± 1.83%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.1330489236790764,
            "range": "± 1.98%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17614.14872798441,
            "range": "± 3.13%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 155.76130528375873,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 148.0355714285714,
            "range": "± 1.12%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.764739726027403,
            "range": "± 2.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.1201174168297494,
            "range": "± 2.06%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.259516634050895,
            "range": "± 1.40%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 1.0065356164383494,
            "range": "± 1.88%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4530289628180002,
            "range": "± 1.58%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.896,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 102.82602739726302,
            "range": "± 1.28%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 108.50147162426403,
            "range": "± 1.06%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 180878.1937377691,
            "range": "± 199.42%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 121508.528375734,
            "range": "± 5.10%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "e55467cb0a9c40e47df39051ab7b8dd34dc6ae17",
          "message": "Do not use \"auto\" time unit",
          "timestamp": "2026-03-21T18:28:34Z",
          "tree_id": "ff700205cba0cdb57620af07493504f2f68ee723",
          "url": "https://github.com/phpactor/phpactor/commit/e55467cb0a9c40e47df39051ab7b8dd34dc6ae17"
        },
        "date": 1774123413475,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.263712328767085,
            "range": "± 1.88%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 165.25783170254576,
            "range": "± 0.81%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.3455068493150266,
            "range": "± 1.38%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.68949315068472,
            "range": "± 1.08%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.033192876712328935,
            "range": "± 1.21%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03493295499021539,
            "range": "± 1.09%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05695448140900139,
            "range": "± 0.89%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.019638590998042743,
            "range": "± 1.50%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.09339272015655607,
            "range": "± 1.36%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.057229119373776796,
            "range": "± 10.11%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.201060273972573,
            "range": "± 0.58%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 543,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1338,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.418234833659564,
            "range": "± 0.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.599330724070485,
            "range": "± 5.81%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09219158512719997,
            "range": "± 2.32%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09227690802348326,
            "range": "± 1.94%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.09163346379647738,
            "range": "± 1.90%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09240039138943187,
            "range": "± 2.86%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.0908953033268101,
            "range": "± 3.56%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.09029589041095949,
            "range": "± 1.63%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.0914234833659499,
            "range": "± 3.57%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6739019569471625,
            "range": "± 1.29%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05543072407045029,
            "range": "± 3.40%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1403424657534246,
            "range": "± 5.62%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13986105675146762,
            "range": "± 6.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13304500978473582,
            "range": "± 7.43%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13411545988258314,
            "range": "± 4.63%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1146360,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08787084148727935,
            "range": "± 4.34%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 297,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 307,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 293,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 78819.12720156556,
            "range": "± 176.66%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 316131.85714285664,
            "range": "± 1.61%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 72504.62426614464,
            "range": "± 2.12%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28704.75929549909,
            "range": "± 0.28%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25413.414872798883,
            "range": "± 0.59%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30440.75146771044,
            "range": "± 0.80%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 817932.7534246517,
            "range": "± 0.64%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 117827,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.6044579256360212,
            "range": "± 1.10%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.1161272015655275,
            "range": "± 0.97%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17245.32876712324,
            "range": "± 0.57%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 151.70543248532172,
            "range": "± 0.40%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 145.80695499021522,
            "range": "± 0.52%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.7435499021526193,
            "range": "± 0.85%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.1077260273972462,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.2398786692759045,
            "range": "± 0.68%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9736119373776734,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4278863013698688,
            "range": "± 0.74%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 6.011,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 99.09261937377451,
            "range": "± 0.69%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 104.74459197651753,
            "range": "± 0.78%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 170856,
            "range": "± 194.65%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 118409.70450097825,
            "range": "± 0.89%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "przepompownia@users.noreply.github.com",
            "name": "Tomasz N",
            "username": "przepompownia"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "77543faa924d1ea336a5284aa146789a3b63fbf0",
          "message": "fix (BinaryExpressionResolver): null coalesce on undefined variable (#3031)",
          "timestamp": "2026-03-21T21:25:17Z",
          "tree_id": "e3117f5546654421addc99308fa2522ad4b43853",
          "url": "https://github.com/phpactor/phpactor/commit/77543faa924d1ea336a5284aa146789a3b63fbf0"
        },
        "date": 1774128418061,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.677794520547955,
            "range": "± 1.91%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 166.0490489236791,
            "range": "± 2.06%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.367587084148717,
            "range": "± 3.19%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.755446183952998,
            "range": "± 0.70%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.03308602739726055,
            "range": "± 1.66%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03479041095890356,
            "range": "± 1.34%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05701409001956958,
            "range": "± 1.78%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.019654403131115484,
            "range": "± 1.83%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.0929769863013701,
            "range": "± 0.96%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.057063365949119677,
            "range": "± 1.57%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.17453855185901,
            "range": "± 0.63%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 587,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1348,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.207334637964705,
            "range": "± 1.19%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.4304911937379,
            "range": "± 0.59%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09020821917808175,
            "range": "± 2.40%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09039178082191605,
            "range": "± 1.60%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.09009452054794514,
            "range": "± 2.61%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09173933463796334,
            "range": "± 2.22%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09134011741683108,
            "range": "± 2.15%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.0909348336594913,
            "range": "± 5.88%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.09123972602739808,
            "range": "± 1.48%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.671474168297457,
            "range": "± 1.67%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05648884540117396,
            "range": "± 8.01%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1400547945205479,
            "range": "± 5.79%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13906457925635998,
            "range": "± 11.71%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13301956947162422,
            "range": "± 4.94%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13421917808219172,
            "range": "± 6.90%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1143783,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08737573385518578,
            "range": "± 6.25%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 290,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 300,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 307,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 77358.14090019569,
            "range": "± 176.79%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 313523.83953033295,
            "range": "± 1.42%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 71220.28571428522,
            "range": "± 0.53%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28487.65949119336,
            "range": "± 0.50%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 24829.876712328747,
            "range": "± 4.20%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30424.655577299083,
            "range": "± 0.37%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 815173.3600782793,
            "range": "± 1.29%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 118617,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5998512720156342,
            "range": "± 1.15%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.0432172211350412,
            "range": "± 1.13%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17012.365949119543,
            "range": "± 0.93%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 149.98205479452085,
            "range": "± 0.85%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 144.91742857142896,
            "range": "± 1.21%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.7272857142857057,
            "range": "± 2.22%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.068929549902121,
            "range": "± 1.18%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.2027358121330995,
            "range": "± 1.05%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9656375733855055,
            "range": "± 0.75%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4074925636007758,
            "range": "± 0.81%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.765,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 96.08085714285743,
            "range": "± 0.58%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 101.49212133072362,
            "range": "± 0.56%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 169425,
            "range": "± 193.93%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 115327.46379647584,
            "range": "± 0.76%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "cb25ad263b9d3aa87f98a6040bdc3194d715766c",
          "message": "gh-3022: explictly specify byte order (#3033)\n\nIf ext-mbstring is not installed, then\nhttps://github.com/symfony/polyfill-mbstring will take over. The\npolyfill uses `iconv`\n\nThere is an off-by-one issue that happens when the ext-mbstring is not\nenabled.\n\n`mbstring` outputs UTF-16BE (first in screenshot) and `iconv` outputs UTF-16LE and also adds BOM (fffe).\n\nBy explicitly specifying the byte order we remove the ambiguity.",
          "timestamp": "2026-03-21T21:25:34Z",
          "tree_id": "dfd0bce59f7ac156b4ed6d2d6477b9d2342bc560",
          "url": "https://github.com/phpactor/phpactor/commit/cb25ad263b9d3aa87f98a6040bdc3194d715766c"
        },
        "date": 1774128434259,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.243017612524469,
            "range": "± 1.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 164.44654207436338,
            "range": "± 1.22%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.350600782778854,
            "range": "± 0.94%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.38650097847323,
            "range": "± 0.60%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.03294465753424704,
            "range": "± 1.58%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.0344785127201566,
            "range": "± 1.94%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05667643835616438,
            "range": "± 5.90%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.019685048923679116,
            "range": "± 3.98%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.09330027397260365,
            "range": "± 0.99%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05751863013698617,
            "range": "± 2.54%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.26336360078275,
            "range": "± 4.02%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 564,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1348,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.383931506849326,
            "range": "± 4.45%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.643782778864784,
            "range": "± 1.19%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09371819960861054,
            "range": "± 2.19%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09061643835616437,
            "range": "± 0.89%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.09146105675146782,
            "range": "± 6.54%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09048590998042941,
            "range": "± 1.90%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09086497064579295,
            "range": "± 3.14%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.09096712328767119,
            "range": "± 6.34%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.09085048923679168,
            "range": "± 1.55%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.684146966731908,
            "range": "± 1.74%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.057526810176126,
            "range": "± 2.46%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.14279256360078268,
            "range": "± 10.59%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1394109589041095,
            "range": "± 10.52%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13397260273972597,
            "range": "± 8.05%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13309197651663401,
            "range": "± 8.67%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1142119,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08997064579256343,
            "range": "± 10.32%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 313,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 290,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 297,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 78822.38551859099,
            "range": "± 176.85%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 316471.7338551874,
            "range": "± 0.52%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 71948.1193737745,
            "range": "± 0.59%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 29019.136986301863,
            "range": "± 1.31%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 24943.92563600788,
            "range": "± 0.34%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30382.87475538144,
            "range": "± 0.36%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 822634.0684931572,
            "range": "± 0.47%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 120976,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.619549902152654,
            "range": "± 1.77%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.0846653620352247,
            "range": "± 0.80%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17273.178082191676,
            "range": "± 0.96%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 153.23224461839598,
            "range": "± 0.30%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 146.69941487279704,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.7439178082191793,
            "range": "± 2.26%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.1178571428571296,
            "range": "± 2.07%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.2376399217221152,
            "range": "± 2.52%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9702248532289631,
            "range": "± 0.73%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4308273972602823,
            "range": "± 0.82%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.786,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 98.65015655577375,
            "range": "± 0.74%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 105.02368590998172,
            "range": "± 0.87%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 171456,
            "range": "± 195.38%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 117940.12915851222,
            "range": "± 1.73%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "613aa65ce2944b44c8fbb92281ce11bd8c9dfbd6",
          "message": "Optimise index service and command (#3037)\n\nThis commit introduces a service to optimise the index.\n\nOptimising currently involces of iterating over all records and pruning\nany records that are defined in non-existing files and removing\n\n- Introduced index iterator\n- Optimizer\n- Add optimiser service that runs every hour by default\n- Add command to manually invoke the optimiser\n- Add LSP notification `phpactor/indexer/optimise` to manually invoke if\n  necessary.",
          "timestamp": "2026-04-13T22:22:49+01:00",
          "tree_id": "ef2b52c248cda116c5ac9866d838659f54e835da",
          "url": "https://github.com/phpactor/phpactor/commit/613aa65ce2944b44c8fbb92281ce11bd8c9dfbd6"
        },
        "date": 1776115474955,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.120994129158479,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 164.89672407044804,
            "range": "± 0.47%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.3416477495107273,
            "range": "± 1.33%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.488827788649985,
            "range": "± 0.92%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.02774054794520524,
            "range": "± 1.40%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.02945060665362051,
            "range": "± 2.46%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.0510578473581215,
            "range": "± 1.08%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.015338943248532563,
            "range": "± 2.04%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08825138943248646,
            "range": "± 1.34%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05772148727984227,
            "range": "± 1.28%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.27180313111546,
            "range": "± 4.17%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 583,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1374,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.228802348336513,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.509851272015464,
            "range": "± 1.45%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09142641878669341,
            "range": "± 3.26%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09080234833659487,
            "range": "± 1.73%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.09024794520547971,
            "range": "± 1.24%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09093933463796396,
            "range": "± 1.76%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.0925344422700595,
            "range": "± 2.04%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.09268610567514682,
            "range": "± 15.79%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.09159041095890276,
            "range": "± 1.78%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.691036594911942,
            "range": "± 1.48%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.055087671232876244,
            "range": "± 3.58%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1373698630136985,
            "range": "± 5.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13763796477495102,
            "range": "± 5.54%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13242465753424648,
            "range": "± 9.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1318160469667318,
            "range": "± 9.22%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1170076,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08807045009784724,
            "range": "± 7.20%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 299,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 334,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 299,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 323342.61056751467,
            "range": "± 126.77%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5786868884540108,
            "range": "± 1.62%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.058835616438339,
            "range": "± 0.94%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17148.30919765151,
            "range": "± 0.93%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 153.27115068493248,
            "range": "± 0.47%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 147.4893796477463,
            "range": "± 0.55%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 74137.30724070473,
            "range": "± 1.60%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 119540,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.7289080234833791,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.0723483365949,
            "range": "± 0.98%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.216870841487279,
            "range": "± 2.01%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.832,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 72445.87475538198,
            "range": "± 0.43%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28599.61643835641,
            "range": "± 0.68%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 24907.06066536205,
            "range": "± 0.67%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30153.420743640534,
            "range": "± 0.70%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 839516.1780821816,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9748589041095872,
            "range": "± 1.30%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4050326810176224,
            "range": "± 0.81%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 173887.7925636008,
            "range": "± 200.96%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 117496.27397260144,
            "range": "± 1.33%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 93.93509393346353,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 100.61395303326775,
            "range": "± 0.31%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "przepompownia@users.noreply.github.com",
            "name": "Tomasz N",
            "username": "przepompownia"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "77f9ff9b50c81300fdd4fecb6fe5f89067cf5cb0",
          "message": "Cleanup after removing PHP 8.1 support (#3038)",
          "timestamp": "2026-04-17T18:06:37+01:00",
          "tree_id": "eb14d4c6199ebed69657adf29fbbec2eb78ba4ed",
          "url": "https://github.com/phpactor/phpactor/commit/77f9ff9b50c81300fdd4fecb6fe5f89067cf5cb0"
        },
        "date": 1776445687060,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 8.532778864970545,
            "range": "± 1.38%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 139.16786301369848,
            "range": "± 0.56%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 1.9265753424657577,
            "range": "± 1.07%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 19.12568688845478,
            "range": "± 1.29%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.015703326810176037,
            "range": "± 1.48%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.0166421526418785,
            "range": "± 1.79%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.036552093933464154,
            "range": "± 1.25%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.007386810176125254,
            "range": "± 3.09%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.0670994911937371,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.046162191780822065,
            "range": "± 1.19%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 14.957459491193763,
            "range": "± 0.32%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 490,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1208,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 10.003994129158404,
            "range": "± 1.13%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 10.045146771037174,
            "range": "± 0.55%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07121291585127144,
            "range": "± 2.27%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.06949178082191756,
            "range": "± 1.88%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07142230919765043,
            "range": "± 1.68%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07196673189823867,
            "range": "± 1.85%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07116634050880613,
            "range": "± 11.42%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07000489236790643,
            "range": "± 1.72%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07134481409001836,
            "range": "± 1.47%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.4373628180039117,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.04874305283757334,
            "range": "± 9.28%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.10993346379647743,
            "range": "± 5.41%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1091369863013698,
            "range": "± 5.36%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.10688062622309193,
            "range": "± 8.01%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.10719178082191774,
            "range": "± 4.25%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 965387,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.07403522504892278,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 261,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 255,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 281,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 273317.22113502934,
            "range": "± 127.32%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.2758864970645731,
            "range": "± 1.42%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.5184207436399135,
            "range": "± 0.91%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 14681.682974559759,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 129.5318551859089,
            "range": "± 0.42%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 124.34115264187753,
            "range": "± 0.20%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 65357.767123288126,
            "range": "± 0.77%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 101122,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.3887847358121328,
            "range": "± 4.11%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.547518590998038,
            "range": "± 1.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 1.7998767123287571,
            "range": "± 0.52%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 4.674,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 58853.36007827805,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 23541.547945205544,
            "range": "± 0.70%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 20997.12328767142,
            "range": "± 1.17%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 26879.43835616402,
            "range": "± 0.37%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 671528.387475532,
            "range": "± 0.16%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.7741129158512686,
            "range": "± 1.35%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.154981409001977,
            "range": "± 0.44%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 134836,
            "range": "± 204.50%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 93162.48532289639,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 76.74860469667334,
            "range": "± 0.65%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 81.55726027397215,
            "range": "± 0.30%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "przepompownia@users.noreply.github.com",
            "name": "Tomasz N",
            "username": "przepompownia"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "4ad4307c8348df4eb525f3a20e5f8ce80a3c4623",
          "message": "Fix PHP 8.5 issues (#2996)\n\nProblem: some phpunit tests fail on PHP 8.5\n\nSolution:\n- upgrade Psalm version\n- upgrade Monolog (avoid: deprecation on the one side, dependency conflicts on the other side)\n- increase test Psalm process timeout to 15 s\n- fix new deprecations\n- add 8.5 to CI matrix",
          "timestamp": "2026-04-18T12:45:39+01:00",
          "tree_id": "23810b35ce27120a535570957da9c4145a599185",
          "url": "https://github.com/phpactor/phpactor/commit/4ad4307c8348df4eb525f3a20e5f8ce80a3c4623"
        },
        "date": 1776512839469,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.953017612524494,
            "range": "± 1.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 163.14431702543664,
            "range": "± 0.45%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.2445205479451777,
            "range": "± 0.90%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.398041095890257,
            "range": "± 0.90%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.027716673189823935,
            "range": "± 1.89%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.02925303326810157,
            "range": "± 1.84%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05049033268101807,
            "range": "± 1.09%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.015377612524461891,
            "range": "± 4.57%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08800861056751598,
            "range": "± 1.16%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05697906066536204,
            "range": "± 8.70%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.11344735812139,
            "range": "± 2.21%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 586,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1385,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 11.932890410959116,
            "range": "± 0.54%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.074583170254256,
            "range": "± 0.60%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.08929236790606704,
            "range": "± 1.84%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.08972230919765159,
            "range": "± 1.62%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.08862994129158508,
            "range": "± 1.23%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.08887260273972601,
            "range": "± 2.27%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.0888857142857154,
            "range": "± 2.02%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.08910645792563528,
            "range": "± 1.39%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.08934716242661354,
            "range": "± 1.33%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6944156555773005,
            "range": "± 11.51%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.0570960861056747,
            "range": "± 3.05%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.13447945205479447,
            "range": "± 9.95%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13359491193737763,
            "range": "± 5.26%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.12902739726027396,
            "range": "± 9.31%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1291526418786692,
            "range": "± 4.84%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1124163,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08671428571428592,
            "range": "± 7.59%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 291,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 311,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 315,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 317073.240704501,
            "range": "± 127.04%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5629354207436637,
            "range": "± 0.60%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.999716242661497,
            "range": "± 0.69%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16231.571428571453,
            "range": "± 0.93%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 150.56836790606735,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 144.20855185909414,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 72099.23679060553,
            "range": "± 0.29%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 117394,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.701450097847341,
            "range": "± 1.39%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.045121330724107,
            "range": "± 0.78%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.177178082191731,
            "range": "± 0.92%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.611,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 70660.02348336583,
            "range": "± 0.51%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28050.958904109393,
            "range": "± 0.37%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 24754.133072406905,
            "range": "± 0.60%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 29742.117416829773,
            "range": "± 3.19%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 815224.1448140729,
            "range": "± 0.71%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9402428571428627,
            "range": "± 0.25%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.3795949119373652,
            "range": "± 0.41%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 157461,
            "range": "± 198.21%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 109095.9784735787,
            "range": "± 0.78%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 88.4603923679057,
            "range": "± 2.26%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 95.46423972602852,
            "range": "± 0.51%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "przepompownia@users.noreply.github.com",
            "name": "Tomasz N",
            "username": "przepompownia"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "6a46d386795040bc7974b1995108336488d90fd2",
          "message": "Allow goto definition from first class callables (#3025)",
          "timestamp": "2026-04-18T12:47:07+01:00",
          "tree_id": "1d0f352ee7dd446f234eae329724fa9decd4a15b",
          "url": "https://github.com/phpactor/phpactor/commit/6a46d386795040bc7974b1995108336488d90fd2"
        },
        "date": 1776512928115,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 8.436596868884452,
            "range": "± 1.41%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 140.61432485322968,
            "range": "± 1.69%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 1.9394931506849433,
            "range": "± 1.90%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 19.223739726027397,
            "range": "± 13.71%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.01564082191780805,
            "range": "± 1.66%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.016819452054794588,
            "range": "± 2.10%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.036789119373777136,
            "range": "± 1.07%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.007395694716242696,
            "range": "± 2.91%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.06670880626223151,
            "range": "± 1.22%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.04671534246575367,
            "range": "± 2.03%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 15.03607397260273,
            "range": "± 1.19%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 499,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1184,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 10.009720156555845,
            "range": "± 1.24%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 10.179788649706543,
            "range": "± 0.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07208473581213193,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.0705835616438339,
            "range": "± 1.81%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07162054794520585,
            "range": "± 0.73%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07161369863013789,
            "range": "± 1.94%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07150547945205557,
            "range": "± 1.95%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07238121330724134,
            "range": "± 2.87%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07142328767123278,
            "range": "± 1.35%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.4391643835616454,
            "range": "± 1.23%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.0482181996086104,
            "range": "± 1.78%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.11434050880626205,
            "range": "± 17.89%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.11104892367906058,
            "range": "± 10.52%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.10709001956947156,
            "range": "± 1.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.10906457925636,
            "range": "± 6.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 983652,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.07416046966731919,
            "range": "± 6.22%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 263,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 254,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 252,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 277500.4520547945,
            "range": "± 127.44%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.293937377690834,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.580371819960894,
            "range": "± 1.04%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 13820.876712328765,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 130.26135616438165,
            "range": "± 0.37%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 127.88866731898463,
            "range": "± 0.47%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 66969.30528376,
            "range": "± 0.64%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 102460,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.3882622309197623,
            "range": "± 1.29%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.552338551859102,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 1.7994285714285747,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 4.681,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 59225.888454010914,
            "range": "± 0.35%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 23710.068493150677,
            "range": "± 0.55%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 21070.939334638606,
            "range": "± 0.52%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 26617.129158512827,
            "range": "± 0.30%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 676307.3561643874,
            "range": "± 0.29%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.7821575342465766,
            "range": "± 1.05%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.156929941291596,
            "range": "± 1.11%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 134346,
            "range": "± 206.05%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 94251.53816047158,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 76.38162426614647,
            "range": "± 0.55%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 82.36578082191662,
            "range": "± 0.98%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "32d4bb041374748dd623e31bfae66079fc2d88be",
          "message": "gh-3039: Resolve additive stub paths consistently (#3040)\n\nUse the same, fully qualified, paths in both the validation listener and\nthe member provider",
          "timestamp": "2026-04-21T11:52:10+01:00",
          "tree_id": "71a86a5422524474833245eace7e275713191e7c",
          "url": "https://github.com/phpactor/phpactor/commit/32d4bb041374748dd623e31bfae66079fc2d88be"
        },
        "date": 1776768830944,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.027684931506954,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 164.98643835616437,
            "range": "± 3.49%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.2911722113502373,
            "range": "± 1.81%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.971904109588873,
            "range": "± 1.08%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.027568023483365938,
            "range": "± 1.47%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.02944814090019572,
            "range": "± 1.08%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05055170254403134,
            "range": "± 7.18%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.01522144814090023,
            "range": "± 6.80%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08740054794520542,
            "range": "± 20.58%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.057169354207436844,
            "range": "± 1.43%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.51428884540117,
            "range": "± 12.21%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 532,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1371,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.17629941291609,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.299285714285876,
            "range": "± 0.65%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09011369863013773,
            "range": "± 1.47%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.0918904109589062,
            "range": "± 2.34%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.08984227005870926,
            "range": "± 2.24%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.08918238747553892,
            "range": "± 1.92%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09114637964774733,
            "range": "± 2.41%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.08949569471624323,
            "range": "± 1.89%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.0892863013698634,
            "range": "± 1.80%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6684410958904174,
            "range": "± 1.39%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.057454207436398765,
            "range": "± 3.51%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1354305283757338,
            "range": "± 4.29%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13716829745596856,
            "range": "± 6.89%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13046183953033264,
            "range": "± 1.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13106457925636,
            "range": "± 6.35%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1132258,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08791389432485304,
            "range": "± 5.38%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 294,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 288,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 305,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 325255.9178082192,
            "range": "± 126.14%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.570391389432491,
            "range": "± 3.19%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.058152641878703,
            "range": "± 1.06%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16663.64383561659,
            "range": "± 0.80%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 151.9192113502936,
            "range": "± 4.90%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 144.6169589041106,
            "range": "± 0.77%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 72629.96086105611,
            "range": "± 0.54%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 116250,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6920450097847315,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.0768082191780595,
            "range": "± 0.64%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.2129178082191685,
            "range": "± 2.17%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.761,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 71614,
            "range": "± 0.84%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28517.794520548003,
            "range": "± 0.53%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 24700.003913894296,
            "range": "± 0.86%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30003.3522504891,
            "range": "± 0.67%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 815288.1682974603,
            "range": "± 0.70%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9484747553816064,
            "range": "± 0.71%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.3964344422700665,
            "range": "± 1.62%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 160596,
            "range": "± 196.87%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 114321.39726027314,
            "range": "± 0.98%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 91.6199070450092,
            "range": "± 1.64%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 99.3857142857132,
            "range": "± 0.91%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "cweiske+github.com-2025@cweiske.de",
            "name": "Christian Weiske",
            "username": "cweiske"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "280ca4fbac604348ce4d9221678ea356c836eb48",
          "message": "Add newline at end of .phpactor.json (#3047)\n\nThis allows us to \"cat .phpactor.json\" without indenting/breaking\nthe shell prompt.\n\nResolves: https://github.com/phpactor/phpactor/issues/3046",
          "timestamp": "2026-05-14T17:58:45+01:00",
          "tree_id": "9387d31381540eab6c4337a6a37562d6cc3ec038",
          "url": "https://github.com/phpactor/phpactor/commit/280ca4fbac604348ce4d9221678ea356c836eb48"
        },
        "date": 1778778030881,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.322675146771038,
            "range": "± 12.38%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.807798434442535,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.644763209393377,
            "range": "± 1.33%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 153.86276908023675,
            "range": "± 0.80%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.031228180039139183,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03279608610567508,
            "range": "± 6.30%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05430692759295405,
            "range": "± 1.01%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.018489628180038867,
            "range": "± 0.98%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08568735812133069,
            "range": "± 1.76%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.054108062622309855,
            "range": "± 1.36%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.504704109589255,
            "range": "± 0.78%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 551,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1365,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 11.667307240704492,
            "range": "± 0.34%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 11.858790606653486,
            "range": "± 0.71%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07528082191781027,
            "range": "± 1.60%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07492407045009819,
            "range": "± 2.24%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07482465753424712,
            "range": "± 2.95%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.0751990215264183,
            "range": "± 3.31%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.0745663405088063,
            "range": "± 13.57%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.0744305283757343,
            "range": "± 2.61%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07545283757338646,
            "range": "± 2.23%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.5963532289628122,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05809041095890428,
            "range": "± 2.55%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1075762,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09342465753424667,
            "range": "± 6.24%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.13623287671232873,
            "range": "± 5.57%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13750684931506843,
            "range": "± 5.89%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13824070450097842,
            "range": "± 1.13%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1372544031311154,
            "range": "± 4.77%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 320,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 324,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 288,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 75461.91780821918,
            "range": "± 176.22%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 288081.7162426652,
            "range": "± 0.56%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6265420743639825,
            "range": "± 3.26%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.9170117416829897,
            "range": "± 1.16%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.098313111545949,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.717,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9197317025440304,
            "range": "± 0.77%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.3351802348336557,
            "range": "± 0.92%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 107493,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 15103.168297456166,
            "range": "± 0.25%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 135.29490410959178,
            "range": "± 0.50%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 129.23354794520677,
            "range": "± 0.25%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.505565557729942,
            "range": "± 21.13%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.8954696673189777,
            "range": "± 1.30%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 61095.85909980555,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 26102.88062622257,
            "range": "± 0.39%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 22802.32876712313,
            "range": "± 0.43%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 29000.727984344187,
            "range": "± 0.40%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 739024.9001956973,
            "range": "± 0.73%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 162355,
            "range": "± 196.02%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 92.86864383561598,
            "range": "± 0.45%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 100.80207729941314,
            "range": "± 0.22%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 113543.88062622352,
            "range": "± 0.65%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "2fa45fa34e28c96ced5653aa2ab7662c3ebff3dc",
          "message": "Gh 3042: No stacking code actions / code-action concurrency (#3048)\n\n- Ensure that only one code-action resolution happens at one time and that any previous operation is cancelled...\n- ... run the action in a separate process so that it's non-blocking (and can therefore also be cancelled).",
          "timestamp": "2026-05-23T07:59:20+01:00",
          "tree_id": "fb63e305a412659316635a8c26576ba37e8b052f",
          "url": "https://github.com/phpactor/phpactor/commit/2fa45fa34e28c96ced5653aa2ab7662c3ebff3dc"
        },
        "date": 1779519645614,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 1.9689823874755312,
            "range": "± 1.17%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 18.716682974559692,
            "range": "± 9.91%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 8.816978473581054,
            "range": "± 1.65%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 142.5283307240695,
            "range": "± 0.51%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.01582739726027385,
            "range": "± 1.57%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.017007240704500694,
            "range": "± 1.23%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.03731545988258358,
            "range": "± 1.84%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.007376438356164418,
            "range": "± 2.90%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.06851346379647681,
            "range": "± 0.95%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.046895107632093286,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 15.567398825831711,
            "range": "± 0.94%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 521,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1242,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 10.174616438355988,
            "range": "± 0.83%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 10.50975146771047,
            "range": "± 0.68%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07332504892367785,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07355479452054807,
            "range": "± 13.67%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07266849315068519,
            "range": "± 2.18%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07176477495107586,
            "range": "± 1.71%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07403091976516799,
            "range": "± 1.59%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07299099804305209,
            "range": "± 2.78%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.0723275929549909,
            "range": "± 2.04%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.417425440313109,
            "range": "± 1.63%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.04818904109589046,
            "range": "± 11.44%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 992279,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.07512915851272016,
            "range": "± 8.55%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.11147945205479448,
            "range": "± 9.57%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.11146575342465746,
            "range": "± 4.28%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.11381017612524451,
            "range": "± 7.75%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.11348727984344413,
            "range": "± 2.86%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 274,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 266,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 274,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 71071.38160469667,
            "range": "± 175.62%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 274116.16046966705,
            "range": "± 0.19%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.4049960861056698,
            "range": "± 1.47%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.5971761252445895,
            "range": "± 0.88%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 1.8397573385518693,
            "range": "± 0.88%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 4.844,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.7971802348336479,
            "range": "± 0.58%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.1701091976516662,
            "range": "± 0.95%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 102623,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 14126.727984344368,
            "range": "± 1.28%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 132.06802739726012,
            "range": "± 0.65%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 126.03719960861135,
            "range": "± 0.64%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.3178238747553626,
            "range": "± 1.23%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.5918160469667697,
            "range": "± 1.36%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 59851.31311154552,
            "range": "± 0.17%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 24375.28767123306,
            "range": "± 0.84%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 21647.966731897483,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 27143.138943248385,
            "range": "± 0.73%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 673126.2074364037,
            "range": "± 0.23%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 138957,
            "range": "± 206.36%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 77.22594814090012,
            "range": "± 15.37%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 83.51342563600798,
            "range": "± 0.54%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 95062.1369863002,
            "range": "± 1.00%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "8b3644d038cefdd8d3e125db2f0675179c43aa89",
          "message": "Update CL",
          "timestamp": "2026-05-30T14:46:19+01:00",
          "tree_id": "c8abbfa6859354d6cf26cde55f6cb0863f897e31",
          "url": "https://github.com/phpactor/phpactor/commit/8b3644d038cefdd8d3e125db2f0675179c43aa89"
        },
        "date": 1780148887442,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.257767123287536,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 166.87138943248567,
            "range": "± 1.73%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.358318982387449,
            "range": "± 1.72%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.802704500978518,
            "range": "± 7.29%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.028396203522505232,
            "range": "± 1.84%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.029691506849315367,
            "range": "± 1.33%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05136399217221141,
            "range": "± 9.74%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.015642739726027435,
            "range": "± 5.31%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08959021526418756,
            "range": "± 1.17%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05794035225048935,
            "range": "± 2.67%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.562893150684665,
            "range": "± 1.04%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 567,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1354,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.423432485322838,
            "range": "± 2.03%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.645187866927582,
            "range": "± 2.03%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09114481409001997,
            "range": "± 2.51%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09241115459882683,
            "range": "± 2.05%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.09223737769080055,
            "range": "± 2.29%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09207475538160662,
            "range": "± 1.86%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09087749510763089,
            "range": "± 1.13%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.09175362035225096,
            "range": "± 2.96%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.09103150684931405,
            "range": "± 2.42%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6536816046966696,
            "range": "± 1.38%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05663170254403166,
            "range": "± 2.72%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1178959,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.14116438356164374,
            "range": "± 4.32%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13963796477495102,
            "range": "± 7.83%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.1337945205479452,
            "range": "± 7.15%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13191976516634046,
            "range": "± 1.86%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08655772994129073,
            "range": "± 2.18%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 293,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 295,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 296,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 325196.2133072407,
            "range": "± 127.96%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16699.181996086016,
            "range": "± 0.99%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 155.25426810176359,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 147.51307045009779,
            "range": "± 1.00%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 75163.6418786681,
            "range": "± 1.09%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.788,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.6093933463796475,
            "range": "± 1.21%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.1073972602738964,
            "range": "± 1.28%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 73177.09197651662,
            "range": "± 0.62%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28910.13502935461,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25451.205479452125,
            "range": "± 1.14%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30038.448140900105,
            "range": "± 0.50%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 835482.2054794456,
            "range": "± 0.60%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.734035225048919,
            "range": "± 1.56%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.087545988258334,
            "range": "± 0.93%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.2053816046966865,
            "range": "± 0.98%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 118722,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9565354207436434,
            "range": "± 0.53%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.405953424657537,
            "range": "± 0.71%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 91.43060469667236,
            "range": "± 0.58%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 99.16330919765171,
            "range": "± 0.99%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 170505.9530332681,
            "range": "± 199.60%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 114118.30919765276,
            "range": "± 1.32%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "902d2f0cc0093cb80048e956abb9cf48745207b5",
          "message": "Include deep-copy as a production dependency",
          "timestamp": "2026-06-01T13:52:38+01:00",
          "tree_id": "9848682b4802746063b5f4dca30d717084f67cc1",
          "url": "https://github.com/phpactor/phpactor/commit/902d2f0cc0093cb80048e956abb9cf48745207b5"
        },
        "date": 1780318466827,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 8.445473581213355,
            "range": "± 2.24%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 137.96150489236916,
            "range": "± 0.43%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 1.8994129158512751,
            "range": "± 6.06%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 18.463260273972637,
            "range": "± 1.34%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.0157720547945205,
            "range": "± 2.80%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.016906692759295343,
            "range": "± 2.57%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.03673780821917822,
            "range": "± 5.62%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.007377651663405106,
            "range": "± 2.43%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.06698164383561796,
            "range": "± 0.72%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.04608176125244633,
            "range": "± 1.27%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 15.112683365949149,
            "range": "± 1.39%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 490,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1171,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 9.996050880625946,
            "range": "± 0.99%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 10.032205479452024,
            "range": "± 0.82%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07152191780822013,
            "range": "± 1.27%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07056849315068489,
            "range": "± 1.60%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07140508806261987,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07149804305283765,
            "range": "± 8.71%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.0706772994129158,
            "range": "± 2.46%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07169452054794574,
            "range": "± 2.12%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.06996164383561561,
            "range": "± 2.73%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.41392876712329,
            "range": "± 1.74%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.047751467710371934,
            "range": "± 3.05%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 964143,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.10932876712328761,
            "range": "± 9.23%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1067534246575342,
            "range": "± 5.09%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.1032583170254403,
            "range": "± 1.23%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.10631506849315064,
            "range": "± 11.08%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.07426810176125238,
            "range": "± 10.84%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 265,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 280,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 271,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 273690.4442270059,
            "range": "± 127.32%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 13778.013698630184,
            "range": "± 0.28%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 128.14741291585136,
            "range": "± 1.29%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 122.80767710372025,
            "range": "± 0.51%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 65141.41095890408,
            "range": "± 0.72%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 4.7,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.2863718199608656,
            "range": "± 1.03%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.5002485322896173,
            "range": "± 0.75%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 58531.722113503056,
            "range": "± 0.45%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 23553.579256360452,
            "range": "± 0.50%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 20958.424657534226,
            "range": "± 1.90%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 26738.534246575415,
            "range": "± 0.34%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 667181.8258316983,
            "range": "± 0.20%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.4029471624266139,
            "range": "± 0.97%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.556033268101731,
            "range": "± 1.12%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 1.8078414872798572,
            "range": "± 0.73%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 100275,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.7752080234833698,
            "range": "± 1.42%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.1365082191780729,
            "range": "± 1.24%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 74.18417416829745,
            "range": "± 0.77%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 80.88467808219217,
            "range": "± 0.58%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 133652,
            "range": "± 203.23%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 91847.10763209351,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "63ed184aafd23eeac340e27884182081f4af4544",
          "message": "Bump composer",
          "timestamp": "2026-06-08T19:18:06+01:00",
          "tree_id": "f15001989dd565a42f17d92abfd5b5f33c44f1b2",
          "url": "https://github.com/phpactor/phpactor/commit/63ed184aafd23eeac340e27884182081f4af4544"
        },
        "date": 1780942802793,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.470015655577198,
            "range": "± 4.83%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 167.57829354207541,
            "range": "± 0.79%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.3874716242661647,
            "range": "± 2.41%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.745962818003825,
            "range": "± 1.22%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.02817851272015657,
            "range": "± 1.39%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.029688062622309247,
            "range": "± 9.58%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.0542113894324854,
            "range": "± 1.27%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.015705988258317073,
            "range": "± 3.29%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.09346551859099876,
            "range": "± 1.22%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05780767123287653,
            "range": "± 2.28%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.335314285714336,
            "range": "± 0.62%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 566,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1355,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.287990215263976,
            "range": "± 1.53%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.49547945205477,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.14427925636007627,
            "range": "± 2.71%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.14565557729941286,
            "range": "± 1.06%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.14664500978473596,
            "range": "± 3.16%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.14822328767123244,
            "range": "± 1.94%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.1494731898238745,
            "range": "± 3.67%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.15053835616438263,
            "range": "± 1.73%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.152057534246574,
            "range": "± 0.82%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6362571428571429,
            "range": "± 1.72%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05610763209393355,
            "range": "± 11.30%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1150691,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1372739726027396,
            "range": "± 10.74%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13719765166340497,
            "range": "± 7.05%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13294716242661433,
            "range": "± 6.31%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.12999804305283755,
            "range": "± 9.42%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08706849315068503,
            "range": "± 18.77%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 298,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 334,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 300,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 325479.3659491194,
            "range": "± 147.90%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16816.569471624563,
            "range": "± 1.43%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 153.56690998043084,
            "range": "± 0.75%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 147.18744814090002,
            "range": "± 0.25%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 73269.6379647745,
            "range": "± 0.78%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.763,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.587264187866939,
            "range": "± 1.68%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.0650156555773393,
            "range": "± 2.17%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 71974.32681017614,
            "range": "± 2.64%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28655.79843444217,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25206.054794520955,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30326.268101760455,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 834246.0684931534,
            "range": "± 0.23%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.729099804305276,
            "range": "± 1.78%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.098095890410975,
            "range": "± 1.74%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.2409549902153056,
            "range": "± 1.27%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 118336,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9724534246575494,
            "range": "± 0.52%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4259606653620127,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 92.70541780821912,
            "range": "± 0.90%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 101.64568688845131,
            "range": "± 0.50%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 166043,
            "range": "± 226.88%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 115641.54011741713,
            "range": "± 1.48%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "3f51172968ce51cd5f4210a0db7e009ecbd897a7",
          "message": "Revert \"Bump composer\"\n\nThis reverts commit 63ed184aafd23eeac340e27884182081f4af4544.",
          "timestamp": "2026-06-11T17:04:32+01:00",
          "tree_id": "9848682b4802746063b5f4dca30d717084f67cc1",
          "url": "https://github.com/phpactor/phpactor/commit/3f51172968ce51cd5f4210a0db7e009ecbd897a7"
        },
        "date": 1781193983935,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.38375342465756,
            "range": "± 2.82%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.58196086105681,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.715127201565618,
            "range": "± 1.65%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 155.61467123287707,
            "range": "± 0.82%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.030885831702544074,
            "range": "± 1.28%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.032358082191780956,
            "range": "± 1.05%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.054339452054794436,
            "range": "± 1.99%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.01860583170254412,
            "range": "± 2.18%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.085620000000001,
            "range": "± 0.82%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05491710371819961,
            "range": "± 7.07%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.40497260273982,
            "range": "± 1.43%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 603,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1389,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 11.977506849315391,
            "range": "± 1.07%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.124994129158488,
            "range": "± 9.09%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07615988258317086,
            "range": "± 2.33%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07729471624266028,
            "range": "± 3.89%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07605088062622499,
            "range": "± 2.20%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07594481409001884,
            "range": "± 2.84%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.0770569471624254,
            "range": "± 1.34%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07722426614481301,
            "range": "± 2.81%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07509628180039016,
            "range": "± 2.14%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.5765504892367852,
            "range": "± 1.41%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05928532289628191,
            "range": "± 9.34%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1106878,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.13811350293542068,
            "range": "± 9.40%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.14221917808219167,
            "range": "± 8.83%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13517221135029342,
            "range": "± 2.59%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13293150684931504,
            "range": "± 0.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09423874755381614,
            "range": "± 10.19%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 293,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 298,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 313,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9258747553816061,
            "range": "± 1.39%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.390265362035277,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.884,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 110670,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 61387.24070450058,
            "range": "± 1.09%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 26352.62230919783,
            "range": "± 0.46%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 23147.019569471526,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 29589.003913894318,
            "range": "± 8.96%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 736329.8806262165,
            "range": "± 0.78%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 15157.722113502889,
            "range": "± 2.79%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 140.85323287671233,
            "range": "± 159.23%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 130.67705479451922,
            "range": "± 0.66%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 292578.7984344419,
            "range": "± 0.77%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.530904109589056,
            "range": "± 1.22%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.924383561643849,
            "range": "± 2.17%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 72534.74363992245,
            "range": "± 0.85%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6394774951076374,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.982878669275928,
            "range": "± 1.20%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.1422641878669557,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 167043,
            "range": "± 202.10%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 97.9244598825858,
            "range": "± 0.51%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 104.39433855185914,
            "range": "± 3.12%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 118124.37769080214,
            "range": "± 0.58%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "4f082b6b734af63286c41ed6e7e73dbde3cbc3b4",
          "message": "Do not show client warning when code action process is killed (#3051)\n\n* Do not show client warning when code action process is killed\n\n* Update changelog",
          "timestamp": "2026-06-11T17:18:30+01:00",
          "tree_id": "9ac559e21eae46137f1a163ff8758ac1189f4a9e",
          "url": "https://github.com/phpactor/phpactor/commit/4f082b6b734af63286c41ed6e7e73dbde3cbc3b4"
        },
        "date": 1781194811249,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.2942152641878955,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.655708414872816,
            "range": "± 2.19%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.059900195694683,
            "range": "± 1.24%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 163.9765068493151,
            "range": "± 1.53%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.02766273972602738,
            "range": "± 2.44%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.02919941291585135,
            "range": "± 1.93%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05087174168297426,
            "range": "± 1.00%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.015392211350293613,
            "range": "± 2.32%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08844606653620378,
            "range": "± 1.67%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05759123287671299,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.307652054794545,
            "range": "± 3.09%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 602,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1379,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.336706457925814,
            "range": "± 1.16%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.24473385518597,
            "range": "± 1.02%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09167318982387669,
            "range": "± 1.13%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09177455968688934,
            "range": "± 2.17%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.09146340508806236,
            "range": "± 6.86%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09257808219178071,
            "range": "± 2.52%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09056301369863005,
            "range": "± 2.93%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.08954266144814005,
            "range": "± 1.22%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.08929393346379642,
            "range": "± 1.59%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.640831506849316,
            "range": "± 5.82%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.058904696673189864,
            "range": "± 7.04%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1132596,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1376399217221134,
            "range": "± 13.18%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13804305283757332,
            "range": "± 7.12%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13200587084148718,
            "range": "± 1.27%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.15585518590997977,
            "range": "± 10.38%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08735812133072397,
            "range": "± 11.42%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 333,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 303,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 292,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 1.0120876712328732,
            "range": "± 8.31%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.407212133072408,
            "range": "± 1.00%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.826,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 118294,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 72050.13894324847,
            "range": "± 0.55%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28552.849315068335,
            "range": "± 0.60%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25078.83953033245,
            "range": "± 0.83%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30130.87671232852,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 817690.6340508802,
            "range": "± 0.62%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16812.10958904054,
            "range": "± 0.64%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 156.5851917808219,
            "range": "± 156.99%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 144.99236594911807,
            "range": "± 0.47%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 317521.6614481397,
            "range": "± 1.17%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.593904109589056,
            "range": "± 1.31%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.1000645792563364,
            "range": "± 1.53%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 73569.79452054751,
            "range": "± 0.59%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.7475909980430733,
            "range": "± 1.57%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.141346379647737,
            "range": "± 1.28%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.2022818003913525,
            "range": "± 1.43%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 162897,
            "range": "± 199.76%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 92.38544618395491,
            "range": "± 1.01%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 98.89009197651717,
            "range": "± 0.30%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 113993.03326810288,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "Marjo.vanLier@gmail.com",
            "name": "Marjo",
            "username": "MarjovanLier"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "1b0834ed333f4e4cce0c74e9e71a06a3ff44d72b",
          "message": "feat(mago): Add Mago diagnostics integration (#3052)\n\nSurface diagnostics from the Mago toolchain (a Rust PHP linter,\nformatter and static analysis tool) in the language server, giving\nusers a fast alternative to the existing PHPStan and Psalm\nintegrations.\n\nTwo providers are registered as an optional extension: \"mago\" runs\n\"mago analyze\" for static analysis and \"mago-lint\" runs \"mago lint\"\nfor style and code smells. The current buffer is streamed to Mago on\nstdin so diagnostics update as you type, and the JSON report is mapped\nto LSP diagnostics with precise byte-offset ranges and related\ninformation for secondary spans.\n\nThe extension is disabled by default. When enabled, analysis is on and\nthe linter is opt-in. A suggestor offers to enable it when the\ncarthage-software/mago Composer package is present.\n\nSigned-off-by: Marjo Wenzel van Lier <marjo.vanlier@gmail.com>",
          "timestamp": "2026-06-25T08:52:20+01:00",
          "tree_id": "da9069554949c96b393230b3bd6a42035e2582fe",
          "url": "https://github.com/phpactor/phpactor/commit/1b0834ed333f4e4cce0c74e9e71a06a3ff44d72b"
        },
        "date": 1782374045041,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.4110665362035215,
            "range": "± 2.92%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.993299412915203,
            "range": "± 0.55%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.030673189823828,
            "range": "± 0.90%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 158.17959491193685,
            "range": "± 1.06%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.031236203522505116,
            "range": "± 1.10%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03253502935420777,
            "range": "± 1.17%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.054747279843443804,
            "range": "± 0.92%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.01845033268101729,
            "range": "± 1.25%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08681514677103727,
            "range": "± 2.15%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05668003913894324,
            "range": "± 6.67%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18.93917181996091,
            "range": "± 4.60%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 643,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1556,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.313614481408978,
            "range": "± 3.01%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.452493150684967,
            "range": "± 0.93%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07693150684931573,
            "range": "± 2.11%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07793698630136874,
            "range": "± 3.38%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07685401174168224,
            "range": "± 2.69%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07712837573385603,
            "range": "± 2.18%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07470939334637962,
            "range": "± 2.24%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07577221135029413,
            "range": "± 2.53%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07624579256360017,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.5976232876712455,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05913561643835601,
            "range": "± 8.74%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1121963,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.14242661448140886,
            "range": "± 5.48%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1405479452054794,
            "range": "± 4.85%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13709980430528368,
            "range": "± 8.05%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13585127201565553,
            "range": "± 2.50%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09280821917808205,
            "range": "± 9.93%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 307,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 304,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 312,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9468119373776904,
            "range": "± 0.67%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.376963013698615,
            "range": "± 0.73%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.916,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 111189,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 62583.01761252482,
            "range": "± 0.99%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 26540.342465753478,
            "range": "± 0.96%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 23276.835616438388,
            "range": "± 0.77%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 29736.373776908076,
            "range": "± 4.07%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 755087.0215264314,
            "range": "± 0.78%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 15553.358121330599,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 144.5134481409002,
            "range": "± 158.34%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 131.9270273972598,
            "range": "± 0.95%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 295331.9941291603,
            "range": "± 0.54%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5178219178082277,
            "range": "± 1.51%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.9676164383561705,
            "range": "± 1.58%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 76437.44031311154,
            "range": "± 0.72%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6674168297455696,
            "range": "± 1.18%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.967682974559695,
            "range": "± 1.12%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.1325205479451674,
            "range": "± 1.07%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 167536,
            "range": "± 205.02%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 97.58942465753404,
            "range": "± 0.47%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 106.50963894324883,
            "range": "± 0.67%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 119114.90802348354,
            "range": "± 3.66%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "be959c4e1b9f3dcefeca0b5971cbcaddff2df3e0",
          "message": "Bump CL",
          "timestamp": "2026-06-26T22:40:56+01:00",
          "tree_id": "6c0f9cc342b1a2984b93c6f9af6334cdf8e5a45a",
          "url": "https://github.com/phpactor/phpactor/commit/be959c4e1b9f3dcefeca0b5971cbcaddff2df3e0"
        },
        "date": 1782510161568,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.2920117416829813,
            "range": "± 1.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.30415264187848,
            "range": "± 1.56%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.082225048923691,
            "range": "± 1.52%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 165.584506849315,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.027727671232876668,
            "range": "± 7.69%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.029059060665361812,
            "range": "± 1.35%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.0508528375733855,
            "range": "± 1.08%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.01531506849315079,
            "range": "± 1.62%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08874109589040982,
            "range": "± 1.29%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05847569471624235,
            "range": "± 1.01%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18.26415068493132,
            "range": "± 0.58%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 545,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1406,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.145727984344427,
            "range": "± 0.69%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.231293542074294,
            "range": "± 0.69%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.08930900195694559,
            "range": "± 2.19%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.08910880626223042,
            "range": "± 3.58%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.0887217221135018,
            "range": "± 1.40%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.08874794520547863,
            "range": "± 2.27%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.088483561643836,
            "range": "± 0.99%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.08894442270058453,
            "range": "± 2.20%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.08723542074363984,
            "range": "± 4.08%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6088158512720203,
            "range": "± 1.48%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05656301369862927,
            "range": "± 2.54%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1140236,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.13756164383561634,
            "range": "± 5.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1372700587084148,
            "range": "± 10.39%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13394520547945193,
            "range": "± 6.77%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13036790606653614,
            "range": "± 2.10%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08479843444226998,
            "range": "± 6.10%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 299,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 293,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 294,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9539796477495088,
            "range": "± 0.94%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.41422407045011,
            "range": "± 0.33%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 6.412,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 119884,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 72485.75146771136,
            "range": "± 0.71%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28828.75146771086,
            "range": "± 1.01%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25143.547945205482,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30034.074363992673,
            "range": "± 0.35%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 827219.4990215079,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16634.42465753432,
            "range": "± 0.79%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 157.08815851272016,
            "range": "± 156.83%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 144.74752641878584,
            "range": "± 0.51%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 317101.4520547934,
            "range": "± 1.46%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5584833659491324,
            "range": "± 1.13%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.0126066536203253,
            "range": "± 0.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 74324.49119373773,
            "range": "± 1.00%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6845107632093759,
            "range": "± 1.47%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.061162426614489,
            "range": "± 1.57%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.1960489236790033,
            "range": "± 1.68%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 165082.78669275928,
            "range": "± 197.26%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 89.25950880626341,
            "range": "± 1.33%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 95.54915655577337,
            "range": "± 0.90%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 112370.17416829779,
            "range": "± 0.79%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "hello@pietro.camp",
            "name": "Pietro Campagnano",
            "username": "fain182"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "3a98f8a111b9009cae3a78c9d5fbf013f8b23954",
          "message": "Require posix extension and fail early when required extensions are missing (#3054)\n\nThe language server crashed mid-session with \"Call to undefined function\nposix_kill()\" on systems without the posix extension, because ext-posix was\nnot declared in composer.json and PHAR distributions bypass Composer's\nplatform checks.\n\n- Declare ext-posix in composer.json so Composer installations fail early\n- Check required extensions on startup in bin/phpactor and exit with a\n  descriptive error instead of crashing during an LSP session\n\nFixes phpactor/phpactor#3053\n\n\nClaude-Session: https://claude.ai/code/session_014Lo55A9LG5DguN2KG792aj\n\nCo-authored-by: Claude <noreply@anthropic.com>",
          "timestamp": "2026-07-05T18:28:54+01:00",
          "tree_id": "88b6670895c02b07e8e0bbc016618c814586a859",
          "url": "https://github.com/phpactor/phpactor/commit/3a98f8a111b9009cae3a78c9d5fbf013f8b23954"
        },
        "date": 1783272641956,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.3165322896281837,
            "range": "± 5.01%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 21.974996086105666,
            "range": "± 0.91%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.517739726027376,
            "range": "± 1.14%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 152.35109393346187,
            "range": "± 0.68%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.030767475538160692,
            "range": "± 1.07%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03256555772994134,
            "range": "± 2.81%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05443589041095884,
            "range": "± 3.06%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.01854270058708425,
            "range": "± 1.14%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08543365949119384,
            "range": "± 3.19%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05501941291585158,
            "range": "± 1.12%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18.059828571428536,
            "range": "± 0.89%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 566,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1452,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.342465753424527,
            "range": "± 1.82%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 11.836630136986365,
            "range": "± 1.19%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07576516634050943,
            "range": "± 3.00%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07561565557729873,
            "range": "± 1.73%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07715322896281678,
            "range": "± 1.58%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07542407045009755,
            "range": "± 1.12%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07551506849315019,
            "range": "± 7.63%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.0767054794520559,
            "range": "± 1.90%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07494285714285827,
            "range": "± 2.66%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.5537287671232953,
            "range": "± 1.59%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.061711937377691144,
            "range": "± 2.36%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1075723,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.1400958904109587,
            "range": "± 18.72%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1341506849315068,
            "range": "± 1.54%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13111350293542068,
            "range": "± 1.41%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1309667318982387,
            "range": "± 4.77%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09116242661448158,
            "range": "± 3.96%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 285,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 284,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 296,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9043835616438349,
            "range": "± 0.79%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.32688493150685,
            "range": "± 4.09%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.501,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 107567,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 59996.109589041385,
            "range": "± 0.99%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 25856.09197651666,
            "range": "± 1.43%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 23002.15655577337,
            "range": "± 0.71%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 29074.569471624236,
            "range": "± 0.33%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 739881.1311154747,
            "range": "± 1.18%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 14957.898238747504,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 139.63541291585128,
            "range": "± 158.43%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 129.0475616438355,
            "range": "± 1.25%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 291936.28571429045,
            "range": "± 0.89%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5237964774951487,
            "range": "± 1.26%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.9308649706457746,
            "range": "± 1.73%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 72378.93542074374,
            "range": "± 7.53%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6185479452054787,
            "range": "± 0.95%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.9079373776908177,
            "range": "± 1.88%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.0662035225048836,
            "range": "± 2.07%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 164084,
            "range": "± 201.77%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 96.92652446184144,
            "range": "± 1.05%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 103.84588943248401,
            "range": "± 1.38%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 115437.33072406985,
            "range": "± 1.24%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "0ba242dce76ebcb7df93dfa39d0d4a1fb05dabbb",
          "message": "Add disclaimer",
          "timestamp": "2026-07-16T22:27:21+01:00",
          "tree_id": "145e102b37b54b769a8d53c85acc43eee2310038",
          "url": "https://github.com/phpactor/phpactor/commit/0ba242dce76ebcb7df93dfa39d0d4a1fb05dabbb"
        },
        "date": 1784237352742,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.807788649706483,
            "range": "± 6.45%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 152.3831898238753,
            "range": "± 0.64%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.34541095890409,
            "range": "± 3.27%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.32214872798452,
            "range": "± 0.78%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.031267788649706446,
            "range": "± 4.66%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03279256360078228,
            "range": "± 1.39%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.055573581213307494,
            "range": "± 2.33%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.018519178082191893,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08780399217221058,
            "range": "± 1.64%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05647812133072408,
            "range": "± 4.90%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18.762213307240653,
            "range": "± 0.65%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 641,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1454,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 11.86464383561647,
            "range": "± 2.69%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.949,
            "range": "± 1.12%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07602720156555613,
            "range": "± 1.99%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07895694716242524,
            "range": "± 4.01%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07593679060665427,
            "range": "± 2.53%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07700587084148716,
            "range": "± 3.52%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07868962818003954,
            "range": "± 2.42%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.0792808219178085,
            "range": "± 4.03%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07722015655577291,
            "range": "± 2.08%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.5547485322896244,
            "range": "± 1.82%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05809706457925636,
            "range": "± 7.93%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1112973,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.13936007827788632,
            "range": "± 11.48%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1482739726027396,
            "range": "± 16.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.15324657534246516,
            "range": "± 6.42%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13128767123287668,
            "range": "± 3.62%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09119178082191795,
            "range": "± 8.46%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 290,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 313,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 353,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9206473581213317,
            "range": "± 4.27%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.3318622309197627,
            "range": "± 0.43%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 15132.849315068466,
            "range": "± 1.40%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 139.4257553816047,
            "range": "± 159.75%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 128.8576575342469,
            "range": "± 0.77%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 60913.21135029393,
            "range": "± 0.82%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 27302.800391389042,
            "range": "± 2.09%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 22845.681017612485,
            "range": "± 1.93%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30550.146771036554,
            "range": "± 1.85%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 739320.5342465728,
            "range": "± 0.99%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.985,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6398297455968822,
            "range": "± 2.20%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.971138943248531,
            "range": "± 1.83%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.1252974559687137,
            "range": "± 2.94%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 76520.76320939271,
            "range": "± 0.55%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5442074363992164,
            "range": "± 1.34%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.908240704500997,
            "range": "± 2.62%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 290932.6477495176,
            "range": "± 0.36%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 113827,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 115264.32289628158,
            "range": "± 3.15%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 169630.72211350294,
            "range": "± 195.38%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 97.90610371820003,
            "range": "± 2.62%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 104.19043933463679,
            "range": "± 1.46%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "13ae9f1ca35b4bb0d35640c1989959341d593bd6",
          "message": "Up",
          "timestamp": "2026-07-16T22:28:06+01:00",
          "tree_id": "8b21e135ac88f4c56a3c1fa74a6ef7168b1d71a9",
          "url": "https://github.com/phpactor/phpactor/commit/13ae9f1ca35b4bb0d35640c1989959341d593bd6"
        },
        "date": 1784237391843,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.59809784735812,
            "range": "± 2.78%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 152.29793737769063,
            "range": "± 0.65%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.3517534246575633,
            "range": "± 1.84%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 21.92788258317004,
            "range": "± 0.69%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.031065205479451997,
            "range": "± 3.01%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.03268273972602718,
            "range": "± 1.11%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.05424626223091982,
            "range": "± 1.17%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.01859178082191751,
            "range": "± 1.84%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08520778864970614,
            "range": "± 1.26%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.055268180039139664,
            "range": "± 1.02%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17.999845401174312,
            "range": "± 0.65%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 564,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1470,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 11.665424657534336,
            "range": "± 2.61%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 11.770054794520576,
            "range": "± 1.40%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07613659491193829,
            "range": "± 2.48%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07490136986301363,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.0749943248532289,
            "range": "± 21.77%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.0763250489236813,
            "range": "± 2.29%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07435988258317024,
            "range": "± 1.84%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07573581213307229,
            "range": "± 2.25%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07426908023483349,
            "range": "± 12.79%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.555475538160473,
            "range": "± 5.35%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.05875479452054777,
            "range": "± 4.89%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1057080,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.13589628180039132,
            "range": "± 7.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.13494129158512716,
            "range": "± 3.38%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.12908219178082184,
            "range": "± 4.39%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1301154598825831,
            "range": "± 4.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09143052837573372,
            "range": "± 11.96%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 316,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 288,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 295,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9019195694716221,
            "range": "± 0.62%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.3139933463796516,
            "range": "± 0.46%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 14818.240704500991,
            "range": "± 1.09%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 137.8949373776908,
            "range": "± 159.27%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 128.37440704501145,
            "range": "± 0.31%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 60321.52641878533,
            "range": "± 0.68%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 25862.424657534015,
            "range": "± 0.64%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 22967.260273972097,
            "range": "± 0.68%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 28920.44422700595,
            "range": "± 0.81%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 725500.3561643853,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.429,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.602397260273974,
            "range": "± 2.30%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.8882524461839596,
            "range": "± 0.56%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.066600782778866,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 72332.67710371842,
            "range": "± 0.47%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.4795596868884533,
            "range": "± 1.63%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.869671232876744,
            "range": "± 1.56%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 285832.9178082163,
            "range": "± 0.19%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 106893,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 113244.87671232982,
            "range": "± 0.57%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 161597,
            "range": "± 196.30%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 91.74155968688859,
            "range": "± 0.54%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 99.8837641878667,
            "range": "± 0.26%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "3d9ca615c64df01df2e96b3c01b7ad55fff3d4db",
          "message": "YMMV",
          "timestamp": "2026-07-16T22:29:37+01:00",
          "tree_id": "fe51bc35a417082e813da2a8bbc4c27a3fdd4ca2",
          "url": "https://github.com/phpactor/phpactor/commit/3d9ca615c64df01df2e96b3c01b7ad55fff3d4db"
        },
        "date": 1784237506240,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 10.54060078277884,
            "range": "± 2.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 168.76145792563509,
            "range": "± 0.84%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.4912152641878658,
            "range": "± 2.06%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 23.192311154598734,
            "range": "± 1.15%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.028458082191780764,
            "range": "± 1.63%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.030160039138943287,
            "range": "± 3.50%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.052535968688846094,
            "range": "± 1.48%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.015612172211350252,
            "range": "± 2.29%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.0929489628180033,
            "range": "± 1.70%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.058555616438355634,
            "range": "± 1.27%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18.6586794520549,
            "range": "± 2.31%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 613,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1444,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 13.076861056751527,
            "range": "± 3.62%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 13.367571428571434,
            "range": "± 25.39%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09487964774951219,
            "range": "± 1.95%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.09278884540117414,
            "range": "± 1.74%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.09518825831702601,
            "range": "± 1.59%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.09323581213307304,
            "range": "± 3.70%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.09301369863013689,
            "range": "± 20.08%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.09219569471624382,
            "range": "± 2.99%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.0924931506849316,
            "range": "± 7.47%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.6261491193737703,
            "range": "± 1.70%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.056698434442270185,
            "range": "± 2.70%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1203789,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.14495107632093915,
            "range": "± 11.64%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1467201565557728,
            "range": "± 5.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.14058904109589027,
            "range": "± 8.56%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13894520547945194,
            "range": "± 10.19%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.086700587084149,
            "range": "± 4.36%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 365,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 317,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 322,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9914702544031404,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4968913894324731,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17094.264187866847,
            "range": "± 1.49%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 164.36882583170257,
            "range": "± 156.80%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 147.67078473581165,
            "range": "± 0.66%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 73015.211350294,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28893.68493150676,
            "range": "± 1.66%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25357.536203522664,
            "range": "± 0.28%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 31357.444227006374,
            "range": "± 0.84%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 846090.315068488,
            "range": "± 1.02%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 6.047,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.798970645792585,
            "range": "± 1.79%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.1808512720156576,
            "range": "± 2.07%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.316397260273983,
            "range": "± 1.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 78996.19960861074,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.6444951076320973,
            "range": "± 2.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.13954207436398,
            "range": "± 1.63%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 323187.2954990186,
            "range": "± 0.71%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 123407,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 119961.71428571377,
            "range": "± 4.72%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 178170.7964774951,
            "range": "± 202.16%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 98.80685420743656,
            "range": "± 0.57%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 109.33638747553633,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "c8e06992eb8bc55993e0e8bf04b1451190895061",
          "message": "Updat README",
          "timestamp": "2026-07-16T22:32:32+01:00",
          "tree_id": "858ad4306124f71138b3a37c757a3b39b7349ca1",
          "url": "https://github.com/phpactor/phpactor/commit/c8e06992eb8bc55993e0e8bf04b1451190895061"
        },
        "date": 1784237662178,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.590178082191859,
            "range": "± 1.70%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 151.78755772994296,
            "range": "± 0.41%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.2653502935420766,
            "range": "± 1.62%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.0360156555771,
            "range": "± 0.85%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.031046849315068323,
            "range": "± 1.38%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.0324877103718197,
            "range": "± 1.54%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.054175499021525866,
            "range": "± 0.83%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.018612720156555438,
            "range": "± 1.03%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.08537534246575439,
            "range": "± 1.10%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05498966731898228,
            "range": "± 3.95%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18.017198043052897,
            "range": "± 0.43%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 571,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1503,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 11.530722113503007,
            "range": "± 0.84%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 11.721238747553732,
            "range": "± 0.50%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07548904109589054,
            "range": "± 11.24%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.07514344422700547,
            "range": "± 5.89%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07413346379647644,
            "range": "± 1.28%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07416086105675258,
            "range": "± 2.09%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07539275929550115,
            "range": "± 1.65%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.07635499021526405,
            "range": "± 2.51%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.0747653620352258,
            "range": "± 1.40%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.5488246575342504,
            "range": "± 1.29%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.058573972602739756,
            "range": "± 9.22%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1129508,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.14418982387475524,
            "range": "± 14.81%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1382876712328766,
            "range": "± 13.71%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.1332348336594911,
            "range": "± 3.88%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1362935420743638,
            "range": "± 4.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09204305283757329,
            "range": "± 9.06%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 378,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 320,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 334,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9650195694716184,
            "range": "± 1.25%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.408073972602737,
            "range": "± 1.73%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 15721.82191780808,
            "range": "± 1.65%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 139.85716438356164,
            "range": "± 159.01%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 129.01768493150703,
            "range": "± 0.54%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 60622.123287671464,
            "range": "± 0.54%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 25844.135029354227,
            "range": "± 0.95%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 22812.729941291764,
            "range": "± 0.31%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 29090.42465753445,
            "range": "± 0.43%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 735192.8121330787,
            "range": "± 0.56%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.529,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6293248532289617,
            "range": "± 2.34%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.9140802348336545,
            "range": "± 0.64%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.0982093933464117,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 72652.45596868917,
            "range": "± 0.75%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.517475538160468,
            "range": "± 1.66%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.994405088062652,
            "range": "± 1.60%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 291459.60273972474,
            "range": "± 0.40%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 110196,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 113900.59491193743,
            "range": "± 1.11%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 162697,
            "range": "± 197.93%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 95.78158317025458,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 104.49149021526412,
            "range": "± 0.39%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "anders@jenbo.dk",
            "name": "Anders Jenbo",
            "username": "AJenbo"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "cb40f25bed7cdd99d3c22d0652d7900f1563d8a9",
          "message": "Code action to add #[Override] (#3056)",
          "timestamp": "2026-07-21T08:46:01+01:00",
          "tree_id": "2d04764ee807c70f102331c659b229cf0d148e26",
          "url": "https://github.com/phpactor/phpactor/commit/cb40f25bed7cdd99d3c22d0652d7900f1563d8a9"
        },
        "date": 1784620056911,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.49085714285705,
            "range": "± 1.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 148.11889823874972,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.1563424657534247,
            "range": "± 24.87%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 19.32651076320909,
            "range": "± 0.61%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.01611217221135028,
            "range": "± 2.56%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.01751185909980411,
            "range": "± 1.60%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.03808559686888406,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.007475225048923644,
            "range": "± 2.59%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.07070418786692763,
            "range": "± 1.36%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.049468180039138984,
            "range": "± 3.05%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 16.373804305283798,
            "range": "± 0.92%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 545,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1312,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 10.741013698630157,
            "range": "± 7.42%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 10.812301369862864,
            "range": "± 1.38%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07399804305283662,
            "range": "± 2.76%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.0736301369863018,
            "range": "± 3.56%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07390136986301363,
            "range": "± 1.57%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.07308493150684904,
            "range": "± 2.59%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07447455968688957,
            "range": "± 1.70%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.0730136986301384,
            "range": "± 1.22%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.07378747553816006,
            "range": "± 2.00%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.3964600782778889,
            "range": "± 1.56%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.04877632093933414,
            "range": "± 2.73%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1051131,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.12005870841487262,
            "range": "± 5.05%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.119665362035225,
            "range": "± 5.46%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.12067123287671211,
            "range": "± 8.94%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.12123483365949105,
            "range": "± 5.79%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.07519373776908049,
            "range": "± 7.61%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 285,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 299,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 286,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.8378700587084239,
            "range": "± 0.86%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.2458911937377686,
            "range": "± 1.15%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 14802.908023483398,
            "range": "± 0.82%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 141.4456927592955,
            "range": "± 156.68%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 132.85969080234852,
            "range": "± 1.39%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 61144.52837573382,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 24874.305283757378,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 22136.868884540087,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 27808.745596868273,
            "range": "± 0.26%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 690800.0547945185,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.002,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.4683835616438279,
            "range": "± 2.01%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.6617827788649593,
            "range": "± 1.60%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 1.8935107632093784,
            "range": "± 1.32%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 72823.64774951147,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.3560919765166406,
            "range": "± 1.21%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.6488610567515063,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 285972.3972602791,
            "range": "± 0.75%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 109860,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 106969.20939334668,
            "range": "± 1.54%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 150477,
            "range": "± 207.82%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 85.84050978473597,
            "range": "± 0.52%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 93.2902583170284,
            "range": "± 0.93%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "781aa80fd281bfa0c1d4aa973723f68e84c6e6c5",
          "message": "Update CL",
          "timestamp": "2026-07-21T08:47:54+01:00",
          "tree_id": "ac6b3041dac6b1d9de8d92cb166d7d702aeb58bd",
          "url": "https://github.com/phpactor/phpactor/commit/781aa80fd281bfa0c1d4aa973723f68e84c6e6c5"
        },
        "date": 1784620175897,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 9.962835616438271,
            "range": "± 2.76%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 162.53335616438358,
            "range": "± 1.31%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.2644070450097584,
            "range": "± 1.76%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 22.46880039138911,
            "range": "± 0.82%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.02794767123287644,
            "range": "± 1.94%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.029855225048923458,
            "range": "± 1.79%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.0510071232876721,
            "range": "± 1.31%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.015402583170254499,
            "range": "± 2.62%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.0884295890410952,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.05808262230919697,
            "range": "± 1.49%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18.142214481409457,
            "range": "± 0.55%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 538,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1394,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12.121504892367943,
            "range": "± 1.78%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12.287655577299377,
            "range": "± 2.97%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.09057142857142864,
            "range": "± 6.70%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.08876868884540166,
            "range": "± 2.67%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.08966947162426608,
            "range": "± 7.15%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.08904970645792501,
            "range": "± 0.97%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.08905851272015647,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.08927906066536198,
            "range": "± 1.73%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.08930489236790512,
            "range": "± 1.95%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.628057534246574,
            "range": "± 11.27%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.06180958904109533,
            "range": "± 4.19%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1130089,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.13879452054794514,
            "range": "± 6.77%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.1370136986301369,
            "range": "± 7.40%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.13149706457925628,
            "range": "± 6.34%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.13219178082191774,
            "range": "± 17.01%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.08532876712328787,
            "range": "± 11.08%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 301,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 296,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 329,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.9572150684931515,
            "range": "± 1.03%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.4110037181995856,
            "range": "± 1.33%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16666.30136986327,
            "range": "± 1.14%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 162.39160469667317,
            "range": "± 155.80%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 144.29274755381653,
            "range": "± 0.54%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 72459.0058708414,
            "range": "± 0.52%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 28571.1506849315,
            "range": "± 1.46%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 25620.47749510732,
            "range": "± 0.86%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 30398.84344422709,
            "range": "± 0.27%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 822448.0000000042,
            "range": "± 0.59%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5.635,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.6828356164383456,
            "range": "± 1.75%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3.0981487279843476,
            "range": "± 5.69%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2.166563600782787,
            "range": "± 1.40%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 74290.63209393554,
            "range": "± 0.16%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.5877945205479447,
            "range": "± 0.82%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3.0214011741682696,
            "range": "± 0.98%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 314577.32485322736,
            "range": "± 0.55%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 116376,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 114999.55577299185,
            "range": "± 1.35%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 164659,
            "range": "± 197.32%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 92.29509393346481,
            "range": "± 0.49%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 101.35176027397375,
            "range": "± 1.01%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "b327eec2cc4b7802b6ca0fcdfdacc2a812368b73",
          "message": "gh-3058: Handle prematurely cancelled code action request (#3059)\n\nImplement cancellation for outsoutced diagnostics\n\nRest the workspace prior to using it",
          "timestamp": "2026-07-21T23:20:54+01:00",
          "tree_id": "e2693e49eabe46ab0c8f45ccf4ecd682d31c2dc9",
          "url": "https://github.com/phpactor/phpactor/commit/b327eec2cc4b7802b6ca0fcdfdacc2a812368b73"
        },
        "date": 1784672566459,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete (short)",
            "value": 8.922068493150691,
            "range": "± 1.14%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete (long)",
            "value": 140.17344227005938,
            "range": "± 0.76%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (short)",
            "value": 2.090275929549914,
            "range": "± 2.14%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete (long)",
            "value": 18.636771037182136,
            "range": "± 0.58%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 0.015549001956947087,
            "range": "± 2.85%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 0.016729393346379677,
            "range": "± 3.47%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 0.03778630136986302,
            "range": "± 2.08%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 0.007483600782778811,
            "range": "± 3.67%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 0.07000164383561619,
            "range": "± 3.28%",
            "unit": "ms",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 0.04762160469667285,
            "range": "± 3.16%",
            "unit": "ms",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 14.711305675146763,
            "range": "± 2.90%",
            "unit": "ms",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 574,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex (1)",
            "value": 1232,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 10.53556947162421,
            "range": "± 4.78%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 10.519180039138796,
            "range": "± 2.04%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1)",
            "value": 0.07022857142857176,
            "range": "± 4.02%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 1001)",
            "value": 0.06893111545988347,
            "range": "± 2.22%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 2001)",
            "value": 0.07085909980430576,
            "range": "± 2.89%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 3001)",
            "value": 0.06991898238747515,
            "range": "± 1.87%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 4001)",
            "value": 0.07126438356164304,
            "range": "± 2.53%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 5001)",
            "value": 0.0712612524461839,
            "range": "± 1.67%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate (length: 6001)",
            "value": 0.0688863013698626,
            "range": "± 3.56%",
            "unit": "ms",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.3515103718199626,
            "range": "± 3.97%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.045326614481408685,
            "range": "± 3.47%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 957775,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (A)",
            "value": 0.11351663405088044,
            "range": "± 7.10%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch (Request)",
            "value": 0.11374755381604688,
            "range": "± 6.43%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (A)",
            "value": 0.11456555772994113,
            "range": "± 4.04%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch (Request)",
            "value": 0.1168003913894324,
            "range": "± 6.48%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.06965557729941318,
            "range": "± 6.20%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 312,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 327,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 299,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 0.8313573385518694,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1.2639622309197458,
            "range": "± 2.08%",
            "unit": "ms",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 14028.851272015589,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 128.46775342465753,
            "range": "± 157.94%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 121.21329354207498,
            "range": "± 0.54%",
            "unit": "ms",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_missing_methods.test)",
            "value": 61339.02935420636,
            "range": "± 0.84%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_generic_objects.test)",
            "value": 24344.15264187893,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (lots_of_new_objects.test)",
            "value": 21745.283757338555,
            "range": "± 0.44%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (method_chain.test)",
            "value": 26833.199608610375,
            "range": "± 0.60%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics (phpstan.test)",
            "value": 665625.992172211,
            "range": "± 0.27%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 4.883,
            "range": "± 0.00%",
            "unit": "ms",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1.449804305283762,
            "range": "± 4.17%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 2.733878669275935,
            "range": "± 1.96%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 1.9091565557730292,
            "range": "± 0.95%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 64767.25831702576,
            "range": "± 0.53%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1.3361937377690742,
            "range": "± 1.21%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 2.6506164383562014,
            "range": "± 1.44%",
            "unit": "ms",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 266429.6712328758,
            "range": "± 0.43%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 99349,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 102418.57142856886,
            "range": "± 0.91%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 146163,
            "range": "± 199.32%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 82.93996966731804,
            "range": "± 0.71%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 89.55038160469663,
            "range": "± 0.64%",
            "unit": "ms",
            "extra": "4 iterations, 2 revs"
          }
        ]
      }
    ]
  }
}