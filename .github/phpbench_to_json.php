<?php

/**
 * Convert PHPBench XML output to the JSON format expected by
 * benchmark-action/github-action-benchmark (customSmallerIsBetter).
 *
 * Usage:
 *   php phpbench_to_json.php phpbench.xml > output.json
 *
 * The script reads the XML dump produced by PHPBench's --dump-file option
 * and emits a JSON array where each entry has:
 *   - name:  benchmark class short name + subject (e.g. "DiagnosticsBench::benchDiagnostics")
 *   - unit:  "μs" (microseconds)
 *   - value: mean time in microseconds
 *   - range: "± {relative standard deviation}%"
 *   - extra: iteration count and revision count
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php phpbench_to_json.php <phpbench-dump.xml>\n");
    exit(1);
}

$file = $argv[1];

if (!file_exists($file)) {
    fwrite(STDERR, "Error: file not found: {$file}\n");
    exit(1);
}

$xml = simplexml_load_file($file);

if ($xml === false) {
    fwrite(STDERR, "Error: could not parse XML file: {$file}\n");
    exit(1);
}

$results = [];

foreach ($xml->suite as $suite) {
    foreach ($suite->benchmark as $benchmark) {
        $className = (string) $benchmark['class'];

        // Use the short class name for readability.
        $shortName = $className;
        if (($pos = strrpos($className, '\\')) !== false) {
            $shortName = substr($className, $pos + 1);
        }

        foreach ($benchmark->subject as $subject) {
            $subjectName = (string) $subject['name'];

            foreach ($subject->variant as $variant) {
                // Build a descriptive name including parameter set if present.
                $paramDesc = '';
                if (isset($variant->parameter_set)) {
                    $params = [];
                    foreach ($variant->parameter_set->parameter as $param) {
                        $params[] = (string) $param['value'];
                    }
                    if (!empty($params)) {
                        $paramDesc = ' (' . implode(', ', $params) . ')';
                    }
                }

                $name = $shortName . '::' . $subjectName . $paramDesc;

                $stats = $variant->stats;

                if (!$stats) {
                    // Fall back to computing from iterations if stats element is missing.
                    $times = [];
                    foreach ($variant->iteration as $iteration) {
                        $revs = (int) $iteration['time-revs'];
                        $netTime = (float) $iteration['time-net'];
                        // time-net is total time for all revs in microseconds.
                        $times[] = $revs > 0 ? $netTime / $revs : $netTime;
                    }

                    if (empty($times)) {
                        continue;
                    }

                    $mean = array_sum($times) / count($times);
                    $rstdev = 0;
                    if (count($times) > 1 && $mean > 0) {
                        $variance = 0;
                        foreach ($times as $t) {
                            $variance += ($t - $mean) ** 2;
                        }
                        $variance /= count($times);
                        $rstdev = (sqrt($variance) / $mean) * 100;
                    }

                    $results[] = [
                        'name' => $name,
                        'unit' => 'μs',
                        'value' => round($mean, 3),
                        'range' => '± ' . round($rstdev, 2) . '%',
                        'extra' => count($times) . ' iterations',
                    ];
                } else {
                    $mean = (float) $stats['mean'];
                    $rstdev = (float) $stats['rstdev'];
                    $iterations = (int) $variant['iterations'];
                    $revs = (int) $variant['revs'];

                    $results[] = [
                        'name' => $name,
                        'unit' => 'μs',
                        'value' => round($mean, 3),
                        'range' => '± ' . round($rstdev, 2) . '%',
                        'extra' => $iterations . ' iterations, ' . $revs . ' revs',
                    ];
                }
            }
        }
    }
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";