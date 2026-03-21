window.BENCHMARK_DATA = {
  "lastUpdate": 1774106835778,
  "repoUrl": "https://github.com/phpactor/phpactor",
  "entries": {
    "Phpactor Benchmarks": [
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
          "id": "b5b5adfffe6437d5ea057b21afd31dd0c85ebd5b",
          "message": "Run PHPBench in CI and store the results on a github page (#3028)",
          "timestamp": "2026-03-09T20:27:12Z",
          "tree_id": "1bed35be2645658643d342d95add617a45969f6c",
          "url": "https://github.com/phpactor/phpactor/commit/b5b5adfffe6437d5ea057b21afd31dd0c85ebd5b"
        },
        "date": 1773088143632,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 10284.5,
            "range": "± 1.87%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 167391.6,
            "range": "± 1.18%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 2403,
            "range": "± 1.86%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 22698.5,
            "range": "± 1.19%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 33.261,
            "range": "± 1.48%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 35.381,
            "range": "± 6.39%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 57.193,
            "range": "± 0.97%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 19.759,
            "range": "± 1.02%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 94.243,
            "range": "± 1.91%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 58.777,
            "range": "± 11.77%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17710.64,
            "range": "± 1.01%",
            "unit": "μs",
            "extra": "0 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 654,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 1417,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12928,
            "range": "± 1.72%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12871.6,
            "range": "± 2.33%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 91.8,
            "range": "± 2.24%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 93.1,
            "range": "± 2.88%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.41,
            "range": "± 2.34%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 92.35,
            "range": "± 10.58%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.73,
            "range": "± 2.43%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 92.2,
            "range": "± 2.78%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.46,
            "range": "± 2.76%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.607,
            "range": "± 1.24%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.057,
            "range": "± 12.85%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 163.1,
            "range": "± 14.47%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 149.7,
            "range": "± 7.19%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 142.7,
            "range": "± 6.3%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 142.3,
            "range": "± 3.53%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1238191,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.091,
            "range": "± 17.01%",
            "unit": "μs",
            "extra": "0 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 327,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 369,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 321,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 651959.2,
            "range": "± 176.16%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 322263.4,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 74153.8,
            "range": "± 1.28%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 29617,
            "range": "± 1.19%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 25673.6,
            "range": "± 1.28%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 31513,
            "range": "± 1%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 837534.8,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 124284,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1661.1,
            "range": "± 3.49%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3184.9,
            "range": "± 2.25%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17544.6,
            "range": "± 1.16%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 160157.6,
            "range": "± 1.75%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 151445.8,
            "range": "± 0.84%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1867.1,
            "range": "± 13.62%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3204.2,
            "range": "± 2.38%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2277.4,
            "range": "± 1.71%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 1006.04,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1516.68,
            "range": "± 1.24%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 6031,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 101883.625,
            "range": "± 0.8%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 109469.5,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 546855.6,
            "range": "± 199.83%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 123037.4,
            "range": "± 1.59%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
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
          "id": "d4c868bbdf296817c8c8deef695c947720a9c540",
          "message": "Test",
          "timestamp": "2026-03-09T20:39:16Z",
          "tree_id": "1bed35be2645658643d342d95add617a45969f6c",
          "url": "https://github.com/phpactor/phpactor/commit/d4c868bbdf296817c8c8deef695c947720a9c540"
        },
        "date": 1773088863807,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 10590.8,
            "range": "± 3.36%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 168639.5,
            "range": "± 1.95%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 2363.7,
            "range": "± 2.17%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 22518.7,
            "range": "± 1.35%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 33.436,
            "range": "± 1.95%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 34.629,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 57.087,
            "range": "± 0.89%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 19.925,
            "range": "± 4.1%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 94.795,
            "range": "± 8.98%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 56.552,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18722.22,
            "range": "± 6.27%",
            "unit": "μs",
            "extra": "0 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 615,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 1383,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12731.1,
            "range": "± 2.39%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12993.1,
            "range": "± 1.42%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 92.97,
            "range": "± 4.97%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.42,
            "range": "± 3.85%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 91.88,
            "range": "± 5.73%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 91.67,
            "range": "± 2.72%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 89.44,
            "range": "± 1.59%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 91.91,
            "range": "± 5.98%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 94.12,
            "range": "± 6.45%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.616,
            "range": "± 3%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.055,
            "range": "± 2.65%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 148.8,
            "range": "± 11.63%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 145.6,
            "range": "± 7.86%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 136.4,
            "range": "± 1.65%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 136.5,
            "range": "± 1.47%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1176184,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.088,
            "range": "± 5.89%",
            "unit": "μs",
            "extra": "0 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 301,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 308,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 328,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 628880.2,
            "range": "± 176.24%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 317251,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 71287.2,
            "range": "± 0.79%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 28427.6,
            "range": "± 1.45%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 24926,
            "range": "± 1.35%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 30318.8,
            "range": "± 1.11%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 816808.4,
            "range": "± 0.65%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 126685,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1613.8,
            "range": "± 1.02%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3059.9,
            "range": "± 1.33%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 16910.4,
            "range": "± 1.57%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 153613.6,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 150199.4,
            "range": "± 1.27%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1766.2,
            "range": "± 2.19%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3091.6,
            "range": "± 0.78%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2244.4,
            "range": "± 1.64%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 987.04,
            "range": "± 0.9%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1459.96,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5836,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 97036.625,
            "range": "± 1%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 104464.25,
            "range": "± 0.77%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 502204.1,
            "range": "± 196.69%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 121664.7,
            "range": "± 1.35%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
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
          "id": "985a2c8676b509e133ac807048d7d7193348d916",
          "message": "Tune the benchmark workflow",
          "timestamp": "2026-03-21T14:12:14Z",
          "tree_id": "a2d17e2c82907197d155f2ee911ec9cc378a62b3",
          "url": "https://github.com/phpactor/phpactor/commit/985a2c8676b509e133ac807048d7d7193348d916"
        },
        "date": 1774102445665,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 10897.9,
            "range": "± 10.12%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 167116.3,
            "range": "± 1.77%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 2413.6,
            "range": "± 3.13%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 22281.5,
            "range": "± 0.92%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 33.31,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 34.677,
            "range": "± 1.27%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 56.569,
            "range": "± 1.08%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 19.792,
            "range": "± 1.25%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 93.458,
            "range": "± 1.37%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 56.832,
            "range": "± 1.83%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17252.66,
            "range": "± 0.47%",
            "unit": "μs",
            "extra": "0 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 552,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 1404,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12569.7,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12693.4,
            "range": "± 2.39%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 89.03,
            "range": "± 2.04%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.25,
            "range": "± 1.54%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 94.26,
            "range": "± 20.89%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 89.05,
            "range": "± 3.23%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.1,
            "range": "± 2.45%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 87.56,
            "range": "± 1.33%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 89.75,
            "range": "± 1.71%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.619,
            "range": "± 4.81%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.055,
            "range": "± 3.46%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 150.4,
            "range": "± 6.05%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 145.8,
            "range": "± 5.12%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 139.1,
            "range": "± 3.89%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 139.6,
            "range": "± 3.61%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1197126,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.092,
            "range": "± 18.05%",
            "unit": "μs",
            "extra": "0 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 301,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 320,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 305,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 635934.6,
            "range": "± 176.04%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 319075.6,
            "range": "± 1.49%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 71531.6,
            "range": "± 0.75%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 28395,
            "range": "± 0.51%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 24926.4,
            "range": "± 1.2%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 30728.6,
            "range": "± 2.27%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 827222,
            "range": "± 1.17%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 122577,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1610.5,
            "range": "± 2%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3071.1,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17450.6,
            "range": "± 3.07%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 153680,
            "range": "± 1.65%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 149146.6,
            "range": "± 0.76%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1761.8,
            "range": "± 2.56%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3187.3,
            "range": "± 3.09%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2209.8,
            "range": "± 1.8%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 989.74,
            "range": "± 1.92%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1421.36,
            "range": "± 0.94%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5841,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 96754.25,
            "range": "± 1.6%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 103973.75,
            "range": "± 0.85%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 506506.7,
            "range": "± 197.26%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 119646.3,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
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
          "id": "bde92b2c5259d59335426fae9259dd603ca4c225",
          "message": "Updat action",
          "timestamp": "2026-03-21T15:25:13Z",
          "tree_id": "fc56fee96db9103d6d373a9a27e6a51c3e552fe3",
          "url": "https://github.com/phpactor/phpactor/commit/bde92b2c5259d59335426fae9259dd603ca4c225"
        },
        "date": 1774106825519,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 10725.4,
            "range": "± 12.65%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 163806.9,
            "range": "± 0.69%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 2328.8,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 23083,
            "range": "± 11.24%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 34.346,
            "range": "± 20.6%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 34.625,
            "range": "± 0.99%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 57.271,
            "range": "± 3.58%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 19.789,
            "range": "± 1.5%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 95.064,
            "range": "± 1.02%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 56.656,
            "range": "± 1.69%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17580,
            "range": "± 1.59%",
            "unit": "μs",
            "extra": "0 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 551,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 1360,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12441.9,
            "range": "± 1.37%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12760.9,
            "range": "± 1.3%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.52,
            "range": "± 2.8%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.4,
            "range": "± 2.24%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.18,
            "range": "± 2.29%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 87.95,
            "range": "± 1.67%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 87.46,
            "range": "± 1.66%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 87.28,
            "range": "± 1.4%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.84,
            "range": "± 2.26%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.601,
            "range": "± 1.51%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.055,
            "range": "± 3.4%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 143.3,
            "range": "± 5.43%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 147.2,
            "range": "± 7.16%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 144.6,
            "range": "± 7.7%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 137.6,
            "range": "± 2.28%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1187391,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.086,
            "range": "± 3.43%",
            "unit": "μs",
            "extra": "0 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 297,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 304,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 324,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 631867.2,
            "range": "± 175.79%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 317299.2,
            "range": "± 0.8%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 71708,
            "range": "± 0.6%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 28665.2,
            "range": "± 3.41%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 24579.8,
            "range": "± 0.8%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 30223.6,
            "range": "± 1.2%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 824301.6,
            "range": "± 0.51%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 118347,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1607.1,
            "range": "± 1.49%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3051.3,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17152.6,
            "range": "± 3.08%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 152895.4,
            "range": "± 1.44%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 146948,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1717.4,
            "range": "± 1.19%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3097.3,
            "range": "± 1.06%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2206.6,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 972.82,
            "range": "± 0.58%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1419.34,
            "range": "± 0.79%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 6048,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 95839,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 104096.875,
            "range": "± 0.67%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 507827.2,
            "range": "± 197.15%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 119230.9,
            "range": "± 1.67%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "name": "phpactor",
            "username": "phpactor"
          },
          "committer": {
            "name": "phpactor",
            "username": "phpactor"
          },
          "id": "5bc9fea157e364ec9165281b2ac1fab63f181283",
          "message": "Phpbench benchmark",
          "timestamp": "2026-03-21T15:25:21Z",
          "url": "https://github.com/phpactor/phpactor/pull/3034/commits/5bc9fea157e364ec9165281b2ac1fab63f181283"
        },
        "date": 1774106835236,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 10307.7,
            "range": "± 1.74%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 166507.7,
            "range": "± 1.3%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 2363,
            "range": "± 2.44%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 22316.8,
            "range": "± 0.8%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 33.103,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 34.517,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 56.793,
            "range": "± 1.56%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 19.772,
            "range": "± 0.78%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 92.952,
            "range": "± 1.37%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 57.025,
            "range": "± 2.18%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 17798.74,
            "range": "± 1.43%",
            "unit": "μs",
            "extra": "0 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 577,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 1403,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12741.5,
            "range": "± 4.04%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 12860.4,
            "range": "± 2.11%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 89.51,
            "range": "± 3.4%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.11,
            "range": "± 1.85%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.46,
            "range": "± 1.29%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.57,
            "range": "± 1.64%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 87.75,
            "range": "± 1.27%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 88.03,
            "range": "± 1.43%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 91.14,
            "range": "± 9.21%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.587,
            "range": "± 0.87%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.055,
            "range": "± 4.33%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 153.2,
            "range": "± 7.89%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 153.5,
            "range": "± 21.71%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 142.7,
            "range": "± 7.17%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 138.6,
            "range": "± 4.25%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1258098,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.09,
            "range": "± 8.78%",
            "unit": "μs",
            "extra": "0 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols",
            "value": 303,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions",
            "value": 323,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 333,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 652098.4,
            "range": "± 176.49%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 321443.4,
            "range": "± 0.49%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 72059.4,
            "range": "± 1.01%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 28482,
            "range": "± 0.92%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 24796.2,
            "range": "± 1.17%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 30376,
            "range": "± 0.71%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 828479,
            "range": "± 0.5%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 126091,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1602.3,
            "range": "± 1.45%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3078.1,
            "range": "± 1.39%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17108.4,
            "range": "± 0.82%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 157534.4,
            "range": "± 1.88%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 148628,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1750.8,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3144.3,
            "range": "± 3.79%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2235,
            "range": "± 2.59%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 983.98,
            "range": "± 1.79%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1415.5,
            "range": "± 1.12%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5769,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 101464.125,
            "range": "± 3.18%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 109119.5,
            "range": "± 2.42%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 561038.2,
            "range": "± 201.33%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 124375.4,
            "range": "± 3.13%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          }
        ]
      }
    ]
  }
}