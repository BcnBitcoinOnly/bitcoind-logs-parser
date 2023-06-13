<?php

declare(strict_types=1);

if ($argc !== 2) {
    die('missing path to logs' . PHP_EOL);
}

$data = [];
$f = fopen($argv[1], 'r');

echo 'timestamp,height,hash,version,log2_work,compact_size_bytes,prefilled_txs,mempool_txs,extra_mempool_txs,requested_txs,validation_time' . PHP_EOL;

while(($line = fgets($f)) !== false) {
    if (preg_match('/^(?<timestamp>.+) Initialized PartiallyDownloadedBlock for block (?<hash>[0-9a-f]+) using a cmpctblock of size (?<size>\d+)/', $line, $matches)) {
        $hash = $matches['hash'];

        if (!isset($data[$hash])) {
            $data[$hash] = [
                'start' => $matches['timestamp'],
                'compact_size' => (int) $matches['size']
            ];
        }

        continue;
    }

    if (preg_match('/^(?<timestamp>.+) Successfully reconstructed block (?<hash>[0-9a-f]+) with (?<prefilled>\d+) txn prefilled, (?<mempool>\d+) txn from mempool \(incl at least (?<extra>\d+) from extra pool\) and (?<requested>\d+) txn requested/', $line, $matches)) {
        $hash = $matches['hash'];

        $data[$hash]['prefilled'] = (int) $matches['prefilled'];
        $data[$hash]['mempool'] = (int) $matches['mempool'];
        $data[$hash]['extra'] = (int) $matches['extra'];
        $data[$hash]['requested'] = (int) $matches['requested'];

        continue;
    }

    if (preg_match('/^(?<timestamp>.+) UpdateTip: new best=(?<hash>[0-9a-f]+) height=(?<height>\d+) version=(?<version>0x[0-9a-f]+) log2_work=(?<work>\d+\.\d+)/', $line, $matches)) {
        $hash = $matches['hash'];

        $data[$hash]['end'] = $matches['timestamp'];
        $data[$hash]['height'] = (int) $matches['height'];
        $data[$hash]['version'] = $matches['version'];
        $data[$hash]['log2_work'] = (float) $matches['work'];

        if (isset($data[$hash]['start'])) {
            $data[$hash]['validation_time'] = (new DateTimeImmutable($data[$hash]['end']))->getTimestamp() - (new DateTimeImmutable($data[$hash]['start']))->getTimestamp();
        }

        echo sprintf(
            '%s,%d,%s,%s,%f,%s,%s,%s,%s,%s,%s' . PHP_EOL,
            $data[$hash]['end'],
            $data[$hash]['height'],
            $hash,
            $data[$hash]['version'],
            $data[$hash]['log2_work'],
            $data[$hash]['compact_size'] ?? 'NULL',
            $data[$hash]['prefilled'] ?? 'NULL',
            $data[$hash]['mempool'] ?? 'NULL',
            $data[$hash]['extra'] ?? 'NULL',
            $data[$hash]['requested'] ?? 'NULL',
            $data[$hash]['validation_time'] ?? 'NULL'
        );

        unset($data[$hash]);
    }
}
