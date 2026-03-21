window.BENCHMARK_DATA = {
  "lastUpdate": 1774108494440,
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
          "id": "73fae0117dd9506efcb81c79d742dafe5f002481",
          "message": "Add dedicated PR workflow",
          "timestamp": "2026-03-21T15:29:35Z",
          "tree_id": "e07e15f5ab18296ac9cdbb8d92e899e7fffeab8e",
          "url": "https://github.com/phpactor/phpactor/commit/73fae0117dd9506efcb81c79d742dafe5f002481"
        },
        "date": 1774107422978,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 10423.2,
            "range": "± 2.55%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete",
            "value": 168207.2,
            "range": "± 0.56%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 2414.8,
            "range": "± 1.24%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete",
            "value": 22704.7,
            "range": "± 1.29%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig",
            "value": 33.117,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder",
            "value": 34.8,
            "range": "± 1.52%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml",
            "value": 57.319,
            "range": "± 2.86%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp",
            "value": 19.801,
            "range": "± 1.7%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig",
            "value": 94.51,
            "range": "± 1.72%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse",
            "value": 57.38,
            "range": "± 1.6%",
            "unit": "μs",
            "extra": "0 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert",
            "value": 18869.92,
            "range": "± 13.65%",
            "unit": "μs",
            "extra": "0 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 586,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex",
            "value": 1362,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics",
            "value": 12882.1,
            "range": "± 1.18%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions",
            "value": 13060.9,
            "range": "± 1.81%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.56,
            "range": "± 1.82%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.82,
            "range": "± 2.68%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.8,
            "range": "± 3.49%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 89.25,
            "range": "± 1.52%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 90.83,
            "range": "± 3.17%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 89.21,
            "range": "± 1.68%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate",
            "value": 92.21,
            "range": "± 2.75%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString",
            "value": 1.588,
            "range": "± 0.92%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens",
            "value": 0.055,
            "range": "± 3.5%",
            "unit": "μs",
            "extra": "0 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 154.8,
            "range": "± 9.8%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch",
            "value": 148.1,
            "range": "± 5.94%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 146.5,
            "range": "± 8.94%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch",
            "value": 145.4,
            "range": "± 4.85%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch",
            "value": 1172554,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName",
            "value": 0.088,
            "range": "± 5.61%",
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
            "value": 312,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols",
            "value": 302,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection",
            "value": 641848,
            "range": "± 176.58%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers",
            "value": 321919.6,
            "range": "± 0.67%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 72885.2,
            "range": "± 1.13%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 28790.4,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 24957.8,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 30473.8,
            "range": "± 0.56%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics",
            "value": 832411.2,
            "range": "± 0.89%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse",
            "value": 121140,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property",
            "value": 1617.7,
            "range": "± 1.31%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type",
            "value": 3106.5,
            "range": "± 1.23%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case",
            "value": 17059.6,
            "range": "± 1.83%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties",
            "value": 159676.2,
            "range": "± 1.58%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames",
            "value": 150526.2,
            "range": "± 0.94%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method",
            "value": 1765.2,
            "range": "± 1.74%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type",
            "value": 3147.2,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type",
            "value": 2253.3,
            "range": "± 1.28%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties",
            "value": 1001.78,
            "range": "± 0.98%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames",
            "value": 1430.32,
            "range": "± 0.7%",
            "unit": "μs",
            "extra": "0 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods",
            "value": 5853,
            "range": "± 0%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion",
            "value": 96536.25,
            "range": "± 0.65%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho",
            "value": 105223.375,
            "range": "± 0.16%",
            "unit": "μs",
            "extra": "0 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete",
            "value": 510952.3,
            "range": "± 198.26%",
            "unit": "μs",
            "extra": "0 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch",
            "value": 117900.1,
            "range": "± 0.94%",
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
          "id": "e06b0fd489255bee847ca40a25f18c8eb7ed6be5",
          "message": "Bump other actions to use PHP 8.2",
          "timestamp": "2026-03-21T15:53:04Z",
          "tree_id": "2b97bb58c2e797fce3cee58e3c9d73039882713a",
          "url": "https://github.com/phpactor/phpactor/commit/e06b0fd489255bee847ca40a25f18c8eb7ed6be5"
        },
        "date": 1774108493816,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ClassMemberCompletorBench::benchComplete#0",
            "value": 10669.896281800295,
            "range": "± 1.82%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassMemberCompletorBench::benchComplete#1",
            "value": 169403.2602739719,
            "range": "± 1.61%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete#0",
            "value": 2483.7123287671084,
            "range": "± 2.48%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorseLocalVariableCompletorBench::benchComplete#1",
            "value": 22961.00978473558,
            "range": "± 1.19%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfig#0",
            "value": 33.51315068493127,
            "range": "± 1.57%",
            "unit": "μs",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithBuilder#0",
            "value": 35.23041095890407,
            "range": "± 11.22%",
            "unit": "μs",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonLoadConfigWithNonExistingYaml#0",
            "value": 57.900547945205496,
            "range": "± 7.07%",
            "unit": "μs",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchJsonPlainPhp#0",
            "value": 19.70356164383558,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "ConfigLoaderBench::benchYamlLoadConfig#0",
            "value": 96.64661448141024,
            "range": "± 1.47%",
            "unit": "μs",
            "extra": "30 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchParse#0",
            "value": 57.946731898238795,
            "range": "± 2.85%",
            "unit": "μs",
            "extra": "33 iterations, 50 revs"
          },
          {
            "name": "PhpactorParserBench::benchAssert#0",
            "value": 17525.263013698477,
            "range": "± 1.23%",
            "unit": "μs",
            "extra": "10 iterations, 5 revs"
          },
          {
            "name": "LexerBench::benchLex#0",
            "value": 656,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "LexerBench::benchLex#1",
            "value": 1412,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchDiagnostics#0",
            "value": 12817.129158512695,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ImportNameProviderBench::benchCodeActions#0",
            "value": 13190.420743639923,
            "range": "± 6.32%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate#0",
            "value": 95.96086105675,
            "range": "± 3.03%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate#1",
            "value": 93.11232876712276,
            "range": "± 2.69%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate#2",
            "value": 94.07162426614411,
            "range": "± 6.06%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate#3",
            "value": 94.55225048923545,
            "range": "± 2.22%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate#4",
            "value": 92.43131115459744,
            "range": "± 3.23%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate#5",
            "value": 91.97064579256389,
            "range": "± 2.49%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "WorkspaceIndexBench::benchUpdate#6",
            "value": 93.07534246575418,
            "range": "± 1.84%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandTokenizedString#0",
            "value": 1.6766592954990287,
            "range": "± 2.20%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "TokenExpanderBench::benchExpandStringWithNoTokens#0",
            "value": 0.056745205479451506,
            "range": "± 3.26%",
            "unit": "μs",
            "extra": "33 iterations, 10000 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch#0",
            "value": 147.9021526418785,
            "range": "± 11.01%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchBareFileSearch#1",
            "value": 145.38356164383555,
            "range": "± 8.11%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch#0",
            "value": 141.91585127201554,
            "range": "± 5.50%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SearchBench::benchFullFileSearch#1",
            "value": 140.84344422700573,
            "range": "± 8.82%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "IndexedReferenceFinderBench::benchBareFileSearch#0",
            "value": 1183010,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ClassRecordShortNameBench::benchShortName#0",
            "value": 0.09038551859099812,
            "range": "± 8.94%",
            "unit": "μs",
            "extra": "33 iterations, 1000 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineCols#0",
            "value": 324,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchLineColsUtf16Positions#0",
            "value": 321,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "EfficientLineColsBench::benchIneffificentLineCols#0",
            "value": 318,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "CarbonReflectBench::benchCarbonReflection#0",
            "value": 82012.5890410959,
            "range": "± 176.36%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "YiiBench::benchMembers#0",
            "value": 324268.590998053,
            "range": "± 0.48%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics#0",
            "value": 73614.99999999937,
            "range": "± 0.81%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics#1",
            "value": 29269.29354207436,
            "range": "± 1.38%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics#2",
            "value": 25770.338551859266,
            "range": "± 0.83%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics#3",
            "value": 30755.41095890406,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "DiagnosticsBench::benchDiagnostics#4",
            "value": 852424.2113502864,
            "range": "± 1.45%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "AnalyserBench::benchAnalyse#0",
            "value": 122670,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property#0",
            "value": 1665.0919765166327,
            "range": "± 6.00%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectPropertyBench::property_return_type#0",
            "value": 3182.2544031311436,
            "range": "± 1.74%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case#0",
            "value": 18001.849315068554,
            "range": "± 0.86%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_methods_and_properties#0",
            "value": 157792.53033268393,
            "range": "± 0.54%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "PhpUnitReflectClassBench::test_case_method_frames#0",
            "value": 150605.46575342567,
            "range": "± 0.81%",
            "unit": "μs",
            "extra": "5 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method#0",
            "value": 1789.1232876712259,
            "range": "± 1.63%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_return_type#0",
            "value": 3188.9119373776557,
            "range": "± 1.46%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ReflectMethodBench::method_inferred_return_type#0",
            "value": 2266.749510763199,
            "range": "± 1.45%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "SelfReflectClassBench::benchMethodsAndProperties#0",
            "value": 1008.8050880626305,
            "range": "± 0.85%",
            "unit": "μs",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "SelfReflectClassBench::benchFrames#0",
            "value": 1454.7295499021614,
            "range": "± 0.90%",
            "unit": "μs",
            "extra": "5 iterations, 10 revs"
          },
          {
            "name": "ReflectionStubsBench::test_classes_and_methods#0",
            "value": 6102,
            "range": "± 0.00%",
            "unit": "μs",
            "extra": "1 iterations, 1 revs"
          },
          {
            "name": "BaseLineBench::benchVersion#0",
            "value": 104282.77201565594,
            "range": "± 1.66%",
            "unit": "μs",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "BaseLineBench::benchRpcEcho#0",
            "value": 109365.47945205499,
            "range": "± 1.44%",
            "unit": "μs",
            "extra": "4 iterations, 2 revs"
          },
          {
            "name": "CompleteBench::benchComplete#0",
            "value": 178594,
            "range": "± 200.08%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ClassSearchBench::benchClassSearch#0",
            "value": 124741.36986301409,
            "range": "± 0.80%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      }
    ]
  }
}