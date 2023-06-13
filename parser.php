<?php

declare(strict_types=1);

if ($argc !== 2) {
    die('missing path to logs' . PHP_EOL);
}

$data = [];
$f = fopen($argv[1], 'r');

echo 'timestamp,height,hash,version,log2_work,compact_size_bytes,prefilled_txs,mempool_txs,extra_mempool_txs,requested_txs,validation_time' . PHP_EOL;

while(($line = fgets($f)) !== false) {
    if (empty($data) && preg_match('/^(?<timestamp>.+) Initialized PartiallyDownloadedBlock for block (?<hash>[0-9a-f]+) using a cmpctblock of size (?<size>\d+)/', $line, $matches)) {
        $data = [
            'start' => $matches['timestamp'],
            'hash' => $matches['hash'],
            'compact_size' => (int) $matches['size']
        ];

        continue;
    }

    if (preg_match('/^(?<timestamp>.+) Successfully reconstructed block (?<hash>[0-9a-f]+) with (?<prefilled>\d+) txn prefilled, (?<mempool>\d+) txn from mempool \(incl at least (?<extra>\d+) from extra pool\) and (?<requested>\d+) txn requested/', $line, $matches)) {
        $data['prefilled'] = (int) $matches['prefilled'];
        $data['mempool'] = (int) $matches['mempool'];
        $data['extra'] = (int) $matches['extra'];
        $data['requested'] = (int) $matches['requested'];

        continue;
    }

    if (preg_match('/^(?<timestamp>.+) UpdateTip: new best=(?<hash>[0-9a-f]+) height=(?<height>\d+) version=(?<version>0x[0-9a-f]+) log2_work=(?<work>\d+\.\d+)/', $line, $matches)) {
        $data['end'] = $matches['timestamp'];
        $data['height'] = (int) $matches['height'];
        $data['version'] = $matches['version'];
        $data['log2_work'] = (float) $matches['work'];

        $data['validation_time'] = (new DateTimeImmutable($data['end']))->getTimestamp() - (new DateTimeImmutable($data['start']))->getTimestamp();

        echo sprintf(
            '%s,%d,%s,%s,%f,%d,%d,%d,%d,%d,%d' . PHP_EOL,
            $data['end'],
            $data['height'],
            $data['hash'],
            $data['version'],
            $data['log2_work'],
            $data['compact_size'],
            $data['prefilled'],
            $data['mempool'],
            $data['extra'],
            $data['requested'],
            $data['validation_time']
        );

        $data = [];
    }
}
