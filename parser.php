<?php

declare(strict_types=1);

if ($argc !== 2) {
    die('missing path to logs' . PHP_EOL);
}

$data = [];
$f = fopen($argv[1], 'r');

echo 'timestamp,height,hash,compact_size,prefilled_txs,mempool_txs,extra_mempool_txs,requested_txs,reconstruct_time,validation_time,total_time' . PHP_EOL;

while(($line = fgets($f)) !== false) {
    if (preg_match('/^(?<timestamp>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[A-Z]+)(?: \[cmpctblock])? Initialized PartiallyDownloadedBlock for block (?<hash>[0-9a-f]+) using a cmpctblock of size (?<size>\d+)/', $line, $matches)) {
        $hash = $matches['hash'];

        if (!isset($data[$hash])) {
            $data[$hash] = [
                'receive_ts' => $matches['timestamp'],
                'compact_size' => (int) $matches['size']
            ];
        }

        continue;
    }

    if (preg_match('/^(?<timestamp>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[A-Z]+)(?: \[cmpctblock])? Successfully reconstructed block (?<hash>[0-9a-f]+) with (?<prefilled>\d+) txn prefilled, (?<mempool>\d+) txn from mempool \(incl at least (?<extra>\d+) from extra pool\) and (?<requested>\d+) txn requested/', $line, $matches)) {
        $hash = $matches['hash'];

        $data[$hash]['reconstruct_ts'] = $matches['timestamp'];
        $data[$hash]['prefilled'] = (int) $matches['prefilled'];
        $data[$hash]['mempool'] = (int) $matches['mempool'];
        $data[$hash]['extra'] = (int) $matches['extra'];
        $data[$hash]['requested'] = (int) $matches['requested'];

        if (isset($data[$hash]['receive_ts'])) {
            $data[$hash]['reconstruct_time'] = (new DateTimeImmutable($data[$hash]['reconstruct_ts']))->getTimestamp() - (new DateTimeImmutable($data[$hash]['receive_ts']))->getTimestamp();
        }

        continue;
    }

    if (preg_match('/^(?<timestamp>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[A-Z]+) UpdateTip: new best=(?<hash>[0-9a-f]+) height=(?<height>\d+) version=0x[0-9a-f]+ log2_work=\d+\.\d+/', $line, $matches)) {
        $hash = $matches['hash'];

        $data[$hash]['commit_ts'] = $matches['timestamp'];
        $data[$hash]['height'] = (int) $matches['height'];

        if (isset($data[$hash]['reconstruct_ts'])) {
            $data[$hash]['validation_time'] = (new DateTimeImmutable($data[$hash]['commit_ts']))->getTimestamp() - (new DateTimeImmutable($data[$hash]['reconstruct_ts']))->getTimestamp();
        }

        if (isset($data[$hash]['receive_ts'])) {
            $data[$hash]['total_time'] = (new DateTimeImmutable($data[$hash]['commit_ts']))->getTimestamp() - (new DateTimeImmutable($data[$hash]['receive_ts']))->getTimestamp();
        }

        echo sprintf(
            '%s,%d,%s,%s,%s,%s,%s,%s,%s,%s,%s' . PHP_EOL,
            $data[$hash]['commit_ts'],
            $data[$hash]['height'],
            $hash,
            $data[$hash]['compact_size'] ?? '',
            $data[$hash]['prefilled'] ?? '',
            $data[$hash]['mempool'] ?? '',
            $data[$hash]['extra'] ?? '',
            $data[$hash]['requested'] ?? '',
            $data[$hash]['reconstruct_time'] ?? '',
            $data[$hash]['validation_time'] ?? '',
            $data[$hash]['total_time'] ?? ''
        );

        unset($data[$hash]);
    }
}
