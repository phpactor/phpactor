window.BENCHMARK_DATA = {
  "lastUpdate": 1776445687941,
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
      }
    ]
  }
}