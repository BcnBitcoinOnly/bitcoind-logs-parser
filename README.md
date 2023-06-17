# bitcoind-logs-parser


## Preparations

Add this configuration line to your `bitcoin.conf` and restart bitcoind:

```
debug=cmpctblock
```

This will prompt your node to start writing extra information in the `debug.log`.
For each new block the node receives, it should go from printing this:

```
2023-06-08T02:23:25Z UpdateTip: new best=000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e height=793329 version=0x246e8000 log2_work=94.228102 tx=849304858 date='2023-06-08T02:23:16Z' progress=1.000000 cache=5.1MiB(29267txo)
```

To this:

```
2023-06-08T02:23:24Z Initialized PartiallyDownloadedBlock for block 000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e using a cmpctblock of size 15535
2023-06-08T02:23:25Z Successfully reconstructed block 000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e with 1 txn prefilled, 1423 txn from mempool (incl at least 55 from extra pool) and 1115 txn requested
2023-06-08T02:23:25Z UpdateTip: new best=000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e height=793329 version=0x246e8000 log2_work=94.228102 tx=849304858 date='2023-06-08T02:23:16Z' progress=1.000000 cache=5.1MiB(29267txo)
```


## Usage

```bash
$ php parser.php path/to/debug.log > table.csv
```


## Sample

```
timestamp,height,hash,compact_size,prefilled_txs,mempool_txs,extra_mempool_txs,requested_txs,reconstruct_time,validation_time,total_time
2023-06-14T18:27:00Z,794344,00000000000000000004f4c16936729ac164c0062d31cee55bc9daa94a89e973,20813,1,3284,4,120,1,3,4
2023-06-14T18:32:39Z,794345,00000000000000000001e5c3e3f9315c72e6acb061a6b8840e070582f9816c5f,13955,1,2024,5,210,3,0,3
2023-06-14T18:34:46Z,794346,00000000000000000001bf4ab1e871003efdf0b1907b477b1a02906d1bb1d16b,16267,1,1743,68,917,2,1,3
2023-06-14T18:35:55Z,794347,00000000000000000004dc0a8a02ebd2f266627cb8297e088a4320231010841e,3501,1,442,67,59,2,0,2
2023-06-14T18:37:37Z,794348,000000000000000000050a3dda9f9e1820a1252d64e885638706b2dd23a888c6,4266,1,588,66,58,2,0,2
2023-06-14T18:42:37Z,794349,000000000000000000025f382c9c981da40c0d867301c08026a20e7464a233ff,11577,1,1418,9,429,1,0,1
2023-06-14T18:49:46Z,794350,0000000000000000000299ff60073cad4389af887c386fb43d44e0b391f09c05,14838,1,1911,37,511,8,0,8
```

Note: even with `debug=cmpctblock` turned on some debug lines can be missing for some of the blocks.
When the script doesn't find all the information it needs to fill in all columns it will leave empty fields, like so:

```
2023-06-12T13:50:33Z,794041,00000000000000000002bffdb280d5792665fc8b5e58f2bb6a2bd794fe9950de,,,,,,,,
```


## Field reference

| Field             | Description                                                               |
|-------------------|---------------------------------------------------------------------------|
| timestamp         | Timestamp at which the block was appended to the blockchain               |
| height            | Block height                                                              |
| hash              | SHA256 hash of the block                                                  |
| compact_size      | Size in bytes of the received compact block header                        |
| prefilled_txs     | Transactions that came with the block (always 1, the coinbase TX)         |
| mempool_txs       | Transactions that were found in the mempool when reconstructing the block |
| extra_mempool_txs | Transactions from the "extra" mempool? Research needed                    |
| requested_txs     | Transactions that had to be requested to peers to reconstruct the block   |
| reconstruct_time  | Time in seconds that it took to reconstruct the compact block             |
| validation_time   | Time in seconds that it took to validate the reconstructed block          |
| total_time        | reconstruct_time + validation_time                                        |
