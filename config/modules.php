<?php

    // Barcode -> parent sample for the water modules. The lab names depend on the selected lab:
    // {lab} is replaced at runtime by 1 (Indonesia) or 2 (Fiji).
    $waterSub = <<<'SQL'
    (SELECT barcode_nitro AS barcode, IF({lab} = 1, 'BTKL Chemistry', 'WAF Chemistry') AS lab, barcode_sample, id_type2bwat
    FROM obj2b_chemistry WHERE LENGTH(barcode_nitro) > 0 AND obj2b_chemistry.lab = '{lab}'
    UNION ALL
    SELECT barcode_nitro2, IF({lab} = 1, 'BBLK Chemistry', 'Other Chemistry'), barcode_sample, id_type2bwat
    FROM obj2b_chemistry WHERE LENGTH(barcode_nitro2) > 0 AND obj2b_chemistry.lab = '{lab}'
    UNION ALL
    SELECT barcode_microbiology, IF({lab} = 1, 'BTKL Micro', 'WAF Micro'), barcode_sample, id_type2bwat
    FROM obj2b_chemistry WHERE LENGTH(barcode_microbiology) > 0 AND obj2b_chemistry.lab = '{lab}'
    UNION ALL
    SELECT barcode_microbiology2, IF({lab} = 1, 'BBLK Micro', 'Other Micro'), barcode_sample, id_type2bwat
    FROM obj2b_chemistry WHERE LENGTH(barcode_microbiology2) > 0 AND obj2b_chemistry.lab = '{lab}'
    UNION ALL
    SELECT barcode_rise_lab, 'RISE Lab', barcode_sample, id_type2bwat
    FROM obj2b_chemistry WHERE LENGTH(barcode_rise_lab) > 0 AND obj2b_chemistry.lab = '{lab}') c
    SQL;

    // Same, but only the two chemistry labs (used by the "conversion" export)
    $waterSubChem = <<<'SQL'
    (SELECT barcode_nitro AS barcode, IF({lab} = 1, 'BTKL Chemistry', 'WAF Chemistry') AS lab, barcode_sample, id_type2bwat
    FROM obj2b_chemistry WHERE LENGTH(barcode_nitro) > 0 AND obj2b_chemistry.lab = '{lab}'
    UNION ALL
    SELECT barcode_nitro2, IF({lab} = 1, 'BBLK Chemistry', 'Other Chemistry'), barcode_sample, id_type2bwat
    FROM obj2b_chemistry WHERE LENGTH(barcode_nitro2) > 0 AND obj2b_chemistry.lab = '{lab}') c
    SQL;

// Microbiology only (the two microbiology barcode columns of obj2b_chemistry)
$waterSubMicro = <<<'SQL'
(SELECT barcode_microbiology AS barcode, IF({lab} = 1, 'BTKL Micro', 'WAF Micro') AS lab, barcode_sample, id_type2bwat
   FROM obj2b_chemistry WHERE LENGTH(barcode_microbiology) > 0 AND obj2b_chemistry.lab = '{lab}'
 UNION ALL
 SELECT barcode_microbiology2, IF({lab} = 1, 'BBLK Micro', 'Other Micro'), barcode_sample, id_type2bwat
   FROM obj2b_chemistry WHERE LENGTH(barcode_microbiology2) > 0 AND obj2b_chemistry.lab = '{lab}') c
SQL;

// Latest location of a freezer bag: newest freezer_in record of the selected lab for that bag
// (as a cryobox, or as a vessel barcode), then its ref_location_80 row of the same lab.
$bagLocation = <<<'SQL'
(SELECT m.id, m.cryobox, f.id_location_80, l.freezer, l.shelf, l.rack, l.rack_level
   FROM (SELECT u.cryobox, MAX(u.id) AS id
           FROM (SELECT cryobox, MAX(id) AS id
                   FROM freezer_in
                  WHERE lab = '{lab}'
                  GROUP BY cryobox
                 UNION ALL
                 SELECT barcode_sample AS cryobox, MAX(id) AS id
                   FROM freezer_in
                  WHERE lab = '{lab}' AND id_vessel <> 1
                  GROUP BY barcode_sample) u
          GROUP BY u.cryobox) m
   LEFT JOIN freezer_in f ON f.id = m.id AND f.lab = '{lab}'
   LEFT JOIN ref_location_80 l ON l.id_location_80 = f.id_location_80 AND l.lab = '{lab}') d
SQL;

// Columns of Freezer OUT, shared by the table, the CSV and the "Used UP" export
$freezerOutColumns = [
    'id'              => ['label' => 'ID',             'csv' => 'ID'],
    'date_out'        => ['label' => 'Date OUT',       'csv' => 'Date_out'],
    'initial'         => ['label' => 'Lab Tech',       'csv' => 'Lab_tech',       'sql' => 'p.initial'],
    'sample'          => ['label' => 'Sample Type',    'csv' => 'Sample_type',    'sql' => 's.sample'],
    'vessel'          => ['label' => 'Vessel Type',    'csv' => 'Vessel_type',    'sql' => 'v.vessel'],
    'barcode_sample'  => ['label' => 'Barcode Vessel', 'csv' => 'Barcode_vessel', 'class' => 'fw-semibold'],
    'destination'     => ['label' => 'Destination',    'csv' => 'Destination',    'sql' => 'e.destination'],
    'shipping_method' => ['csv' => 'Shipping_method', 'sql' => 'f.shipping_method', 'export_only' => true],
    'tracking_number' => ['csv' => 'Tracking_number', 'export_only' => true],
    'comments'        => ['label' => 'Comments', 'csv' => 'Comments', 'class' => 'col-trunc'],
];

return [

    // ============================================================ OBJECTIVE 3

    'o3_sample_reception' => [
        'title' => 'Sample Reception',
        'table' => 'obj3_sam_rec',
        'joins' => [
            ['ref_person as p',     'p.id_person',     't.id_person'],
            ['ref_sampletype as s', 's.id_sampletype', 't.id_type'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode',          'csv' => 'Barcode_sample', 'class' => 'fw-semibold', 'width' => '230px'],
            'date_receipt'   => ['label' => 'Date Receipt',     'csv' => 'Date_receipt'],
            'time_receipt'   => ['label' => 'Time Receipt',     'csv' => 'Time_receipt', 'sql' => "DATE_FORMAT(t.time_receipt, '%H:%i')"],
            'initial'        => ['label' => 'Received By',      'csv' => 'Lab_tech',     'sql' => 'p.initial'],
            'sample_type'    => ['label' => 'Sample Type',      'csv' => 'Sample_type',  'sql' => 's.sampletype'],
            'png_control'    => ['label' => 'PNG Control',      'csv' => 'PnG_control'],
            'cold_chain'     => ['label' => 'Cold Chain',       'csv' => 'Cold_chain'],
            'cont_intact'    => ['label' => 'Container Intact', 'csv' => 'Cont_intact'],
            'comments'       => ['label' => 'Comments',         'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_receipt', 'desc'], ['time_receipt', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o3_blood_centrifuge' => [
        'title' => 'Blood - Centrifuge',
        'table' => 'obj3_blood_centrifuge',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'id'              => ['label' => 'ID',              'csv' => 'ID'],
            'date_process'    => ['label' => 'Date Process',    'csv' => 'Date_process'],
            'initial'         => ['label' => 'Lab Tech',        'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'centrifuge_time' => ['label' => 'Centrifuge Time', 'csv' => 'Centrifuge_time', 'sql' => "DATE_FORMAT(t.centrifuge_time, '%H:%i')"],
            'comments'        => ['label' => 'Comments',        'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['centrifuge_time', 'desc'], ['id', 'asc']],

        // click "+" on a row to see the samples of that session
        'detail' => [
            'table'     => 'obj3_blood_centrifuge_det',
            'key'       => 'd.id_bc',     // detail column that points to the parent
            'parent'    => 't.id',        // parent column
            'id_column' => 'id',          // visible column above that holds the parent id
            'columns'   => [
                'barcode_sample' => ['label' => 'Barcode Sample'],
                'comments'       => ['label' => 'Comments'],
            ],
            'order' => 'd.barcode_sample',
        ],

        // the CSV is flattened: one line per sample (child rows must belong to the same lab and not be deleted)
        'export' => [
            'table' => 'obj3_blood_centrifuge',
            'joins' => [
                ['ref_person as p', 'p.id_person', 't.id_person'],
                ['obj3_blood_centrifuge_det as d', 'd.id_bc', 't.id', ['d.lab = t.lab', 'd.flag = 0']],
            ],
            'columns' => [
                'id'              => ['label' => 'ID',              'csv' => 'ID'],
                'date_process'    => ['label' => 'Date Process',    'csv' => 'Date_process'],
                'initial'         => ['label' => 'Lab Tech',        'csv' => 'Lab_tech', 'sql' => 'p.initial'],
                'centrifuge_time' => ['label' => 'Centrifuge Time', 'csv' => 'Centrifuge_time', 'sql' => "DATE_FORMAT(t.centrifuge_time, '%H:%i')"],
                'comments'        => ['label' => 'Comments',        'csv' => 'Comments'],
                'barcode_sample'  => ['label' => 'Barcode Sample',  'csv' => 'Barcode_sample',  'sql' => 'd.barcode_sample'],
                'comments_sample' => ['label' => 'Comments Sample', 'csv' => 'Comments_sample', 'sql' => 'd.comments'],
            ],
            'order' => [['date_process', 'desc'], ['centrifuge_time', 'desc'], ['id', 'asc'], ['barcode_sample', 'asc']],
        ],
    ],

    'o3_blood_edta' => [
        'title' => 'Blood - EDTA Aliquots',
        'table' => 'obj3_edta_aliquots',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode Sample', 'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_process'   => ['label' => 'Date Process',   'csv' => 'Date_process'],
            'initial'        => ['label' => 'Lab Tech',       'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'hemolysis'      => ['label' => 'Hemolysis',      'csv' => 'Hemolysis'],
            'barcode_wb'     => ['label' => 'Barcode WB',     'csv' => 'Barcode_wb'],
            'vol_aliquotwb'  => ['label' => 'Vol. Aliquot WB', 'csv' => 'Vol_aliquotwb', 'export_only' => true],
            'cryoboxwb'      => ['label' => 'Cryobox WB',     'csv' => 'Cryoboxwb',      'export_only' => true],
            'barcode_p1a'    => ['label' => 'Bar Aliquot 1',  'csv' => 'Barcode_plasma1'],
            'vol_aliquot1'   => ['label' => 'Vol. Aliquot 1', 'csv' => 'Vol_aliquot1',   'export_only' => true],
            'cryobox1'       => ['label' => 'Cryobox 1',      'csv' => 'Cryobox1',       'export_only' => true],
            'barcode_p2a'    => ['label' => 'Bar Aliquot 2',  'csv' => 'Barcode_plasma2'],
            'vol_aliquot2'   => ['label' => 'Vol. Aliquot 2', 'csv' => 'Vol_aliquot2',   'export_only' => true],
            'cryobox2'       => ['label' => 'Cryobox 2',      'csv' => 'Cryobox2',       'export_only' => true],
            'barcode_p3a'    => ['label' => 'Bar Aliquot 3',  'csv' => 'Barcode_plasma3'],
            'vol_aliquot3'   => ['label' => 'Vol. Aliquot 3', 'csv' => 'Vol_aliquot3',   'export_only' => true],
            'cryobox3'       => ['label' => 'Cryobox 3',      'csv' => 'Cryobox3',       'export_only' => true],
            'packed_cells'   => ['label' => 'Packed Cells',   'csv' => 'Packed_cells'],
            'cryobox_pc'     => ['label' => 'Cryobox PC',     'csv' => 'Cryobox_PC',     'export_only' => true],
            'comments'       => ['label' => 'Comments',       'csv' => 'Comments',       'export_only' => true],
        ],
        'order' => [['date_process', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o3_blood_sst' => [
        'title' => 'Blood - SST Aliquots',
        'table' => 'obj3_sst_aliquots',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode',        'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_process'   => ['label' => 'Date Process',   'csv' => 'Date_process'],
            'initial'        => ['label' => 'Lab Tech',       'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'barcode_sst1'   => ['label' => 'Barcode SST 1',  'csv' => 'Barcode_SST1'],
            'vol_aliquot1'   => ['label' => 'Vol. Aliquot 1', 'csv' => 'Vol_aliquot1'],
            'cryobox1'       => ['label' => 'Cryobox 1',      'csv' => 'Cryobox1'],
            'barcode_sst2'   => ['label' => 'Barcode SST 2',  'csv' => 'Barcode_SST2'],
            'vol_aliquot2'   => ['label' => 'Vol. Aliquot 2', 'csv' => 'Vol_aliquot2'],
            'cryobox2'       => ['label' => 'Cryobox 2',      'csv' => 'Cryobox2'],
            'comments'       => ['label' => 'Comments',       'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o3_filter_paper' => [
        'title' => 'Blood - Filter Paper',
        'table' => 'obj3_bfilterpaper',
        'joins' => [
            ['ref_person as p',      'p.id_person',      't.id_person'],
            ['ref_location_80 as l', 'l.id_location_80', 't.id_location_80', ['l.lab = t.lab']],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode',      'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_process'   => ['label' => 'Date Process', 'csv' => 'Date_process'],
            'time_process'   => ['label' => 'Time Process', 'csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
            'initial'        => ['label' => 'Lab Tech',     'csv' => 'Lab_tech',     'sql' => 'p.initial'],
            'freezer_bag'    => ['label' => 'Freezer Bag',  'csv' => 'Freezer_bag'],
            'location'       => ['label' => 'Location',     'csv' => 'Location',
                                 'sql' => "CONCAT('F', l.freezer, '-', 'S', l.shelf, '-', 'R', l.rack, '-', 'DRW', l.rack_level)"],
            'comments'       => ['label' => 'Comments',     'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['barcode_sample', 'asc']],

        // the CSV shows the latest location of the freezer bag (same logic as the old file)
        'export' => [
            'joins' => [
                ['ref_person as p', 'p.id_person', 't.id_person'],
                [$bagLocation, 'd.cryobox', 't.freezer_bag'],
            ],
            'columns' => [
                'barcode_sample' => ['csv' => 'Barcode_sample'],
                'date_process'   => ['csv' => 'Date_process'],
                'time_process'   => ['csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
                'initial'        => ['csv' => 'Lab_tech',     'sql' => 'p.initial'],
                'freezer_bag'    => ['csv' => 'Freezer_bag'],
                'location'       => ['csv' => 'Location',
                                     'sql' => "CONCAT('F', d.freezer, '-', 'S', d.shelf, '-', 'R', d.rack, '-', 'DRW', d.rack_level)"],
                'comments'       => ['csv' => 'Comments'],
            ],
            'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['barcode_sample', 'asc']],
        ],
    ],

    'o3_feces_kk1' => [
        'title' => 'Feces - KK 1',
        'table' => 'obj3_fkk1',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode',          'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_process'   => ['label' => 'Date Process',     'csv' => 'Date_process'],
            'time_process'   => ['label' => 'Time Process',     'csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
            'initial'        => ['label' => 'Lab Tech',         'csv' => 'Lab_tech',     'sql' => 'p.initial'],
            'bar_kkslide'    => ['label' => 'Barcode KK Slide', 'csv' => 'Bar_kkslide'],
            'comments'       => ['label' => 'Comments',         'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o3_feces_aliquot' => [
        'title' => 'Feces - Aliquot',
        'table' => 'obj3_faliquot',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode',           'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_process'   => ['label' => 'Date Process',      'csv' => 'Date_process'],
            'time_process'   => ['label' => 'Time Process',      'csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
            'initial'        => ['label' => 'Lab Tech',          'csv' => 'Lab_tech',     'sql' => 'p.initial'],
            'cons_stool'     => ['label' => 'Stool Consistency', 'csv' => 'Cons_stool'],
            'color_stool'    => ['label' => 'Stool Color',       'csv' => 'Color_stool'],
            'abnormal'       => ['label' => 'Abnormal',          'csv' => 'Abnormal'],
            'ab_other'       => ['label' => 'Other Abnormal',    'csv' => 'Other_abnormal'],
            'aliquot1'       => ['label' => 'Aliquot 1',         'csv' => 'Aliquot1'],
            'volume1'        => ['label' => 'Volume 1',          'csv' => 'Volume1'],
            'cryobox1'       => ['label' => 'Cryobox 1',         'csv' => 'Cryobox1'],
            'aliquot2'       => ['label' => 'Aliquot 2',         'csv' => 'Aliquot2'],
            'volume2'        => ['label' => 'Volume 2',          'csv' => 'Volume2'],
            'cryobox2'       => ['label' => 'Cryobox 2',         'csv' => 'Cryobox2'],
            'aliquot3'       => ['label' => 'Aliquot 3',         'csv' => 'Aliquot3'],
            'volume3'        => ['label' => 'Volume 3',          'csv' => 'Volume3'],
            'cryobox3'       => ['label' => 'Cryobox 3',         'csv' => 'Cryobox3'],
            'aliquot_zymo'   => ['label' => 'Aliquot Zymo',      'csv' => 'Aliquot_zymo'],
            'volume_zymo'    => ['label' => 'Volume Zymo',       'csv' => 'Volume_zymo'],
            'batch_zymo'     => ['label' => 'Batch Zymo',        'csv' => 'Batch_zymo'],
            'cryobox_zymo'   => ['label' => 'Cryobox Zymo',      'csv' => 'Cryobox_zymo'],
            'comments'       => ['label' => 'Comments',          'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o3_feces_mac1' => [
        'title' => 'Feces - Macconkey 1',
        'table' => 'obj3_fmac1',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode',           'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_process'   => ['label' => 'Date Process',      'csv' => 'Date_process'],
            'time_process'   => ['label' => 'Time Process',      'csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
            'initial'        => ['label' => 'Lab Tech',          'csv' => 'Lab_tech',     'sql' => 'p.initial'],
            'bar_macconkey'  => ['label' => 'Barcode Macconkey', 'csv' => 'Barcode_macconkey'],
            'comments'       => ['label' => 'Comments',          'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o3_feces_mac2' => [
        'title' => 'Feces - Macconkey 2',
        'table' => 'obj3_fmac2',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'bar_macconkey' => ['label' => 'Barcode Macconkey',  'csv' => 'Barcode_macconkey', 'class' => 'fw-semibold'],
            'date_process'  => ['label' => 'Date Process',       'csv' => 'Date_process'],
            'time_process'  => ['label' => 'Time Process',       'csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
            'initial'       => ['label' => 'Lab Tech',           'csv' => 'Lab_tech',     'sql' => 'p.initial'],
            'bar_macsweep1' => ['label' => 'Barcode Macsweep 1', 'csv' => 'Barcode_macsweep1'],
            'cryobox1'      => ['label' => 'Cryobox 1',          'csv' => 'Cryobox1'],
            'bar_macsweep2' => ['label' => 'Barcode Macsweep 2', 'csv' => 'Barcode_macsweep2'],
            'cryobox2'      => ['label' => 'Cryobox 2',          'csv' => 'Cryobox2'],
            'comments'      => ['label' => 'Comments',           'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['bar_macconkey', 'asc']],
    ],

    'o3_feces_kk2' => [
        'title' => 'Feces - KK 2',
        'table' => 'obj3_fkk2',
        'joins' => [
            ['ref_person as p1', 'p1.id_person', 't.id_person'],    // person read
            ['ref_person as p2', 'p2.id_person', 't.id_person2'],   // person write
        ],
        'columns' => [
            'bar_kkslide'       => ['label' => 'Barcode KK Slide',    'csv' => 'Barcode_kkslide', 'class' => 'fw-semibold'],
            'date_process'      => ['label' => 'Date Process',        'csv' => 'Date_process'],
            'person_read'       => ['label' => 'Person Read',         'csv' => 'Person_read',  'sql' => 'p1.initial'],
            'person_write'      => ['label' => 'Person Write',        'csv' => 'Person_write', 'sql' => 'p2.initial'],
            'duration'          => ['label' => 'Duration',            'csv' => 'Duration'],
            'start_time'        => ['label' => 'Start Time',          'csv' => 'Start_time'],
            'end_time'          => ['label' => 'End Time',            'csv' => 'End_time'],
            'ascaris'           => ['label' => 'Ascaris',             'csv' => 'Ascaris'],
            'ascaris_com'       => ['label' => 'Ascaris Note',        'csv' => 'Ascaris_note'],
            'hookworm'          => ['label' => 'Hookworm',            'csv' => 'Hookworm'],
            'hookworm_com'      => ['label' => 'Hookworm Note',       'csv' => 'Hookworm_note'],
            'trichuris'         => ['label' => 'Trichuris',           'csv' => 'Trichuris'],
            'trichuris_com'     => ['label' => 'Trichuris Note',      'csv' => 'Trichuris_note'],
            'strongyloides'     => ['label' => 'Strongyloides',       'csv' => 'Strongyloides'],
            'strongyloides_com' => ['label' => 'Strongyloides Note',  'csv' => 'Strongyloides_note'],
            'taenia'            => ['label' => 'Taenia',              'csv' => 'Taenia'],
            'taenia_com'        => ['label' => 'Taenia Note',         'csv' => 'Taenia_note'],
            'other'             => ['label' => 'Other',               'csv' => 'Other'],
            'other_com'         => ['label' => 'Other Note',          'csv' => 'Other_note'],
            'comments'          => ['label' => 'Comments',            'csv' => 'Comments', 'class' => 'col-trunc'],
            'finalized'         => ['label' => 'Finalized',           'csv' => 'Finalized'],
        ],
        'order' => [['date_process', 'desc'], ['bar_kkslide', 'asc']],
    ],

    // ============================================================ OBJECTIVE 2A

    'o2a_sample_reception' => [
        'title' => 'Sample Reception (2A)',
        'table' => 'obj2a_receipt',
        'joins' => [
            ['ref_person as p1', 'p1.id_person', 't.id_delivered'],
            ['ref_person as p2', 'p2.id_person', 't.id_received'],
        ],
        'columns' => [
            'id_receipt'   => ['label' => 'ID',           'csv' => 'ID'],
            'date_receipt' => ['label' => 'Date Receipt', 'csv' => 'Date_receipt'],
            'delivered'    => ['label' => 'Delivered',    'csv' => 'Delivered_by', 'sql' => 'p1.initial'],
            'received'     => ['label' => 'Received',     'csv' => 'Received_by',  'sql' => 'p2.initial'],
            'sample_type'  => ['label' => 'Sample Type',  'csv' => 'Sample_type'],
        ],
        'order' => [['date_receipt', 'desc'], ['id_receipt', 'asc']],

        // click "+" next to the ID to see the barcodes of that receipt
        'detail' => [
            'table'     => 'obj2a_receipt_det',
            'key'       => 'd.id_receipt',
            'parent'    => 't.id_receipt',
            'id_column' => 'id_receipt',
            'columns'   => [
                'barcode_sample' => ['label' => 'Barcode Storage Bag'],
            ],
            'order' => 'd.barcode_sample',
        ],

        // the CSV is flattened: one line per barcode
        'export' => [
            'table' => 'obj2a_receipt',
            'joins' => [
                ['obj2a_receipt_det as d', 'd.id_receipt', 't.id_receipt', ['d.lab = t.lab', 'd.flag = 0']],
                ['ref_person as p1', 'p1.id_person', 't.id_delivered'],
                ['ref_person as p2', 'p2.id_person', 't.id_received'],
            ],
            'where'   => ['d.barcode_sample IS NOT NULL'],
            'columns' => [
                'barcode_sample' => ['label' => 'Barcode Storage Bag', 'csv' => 'Barcode_storagebag', 'sql' => 'd.barcode_sample'],
                'date_receipt'   => ['label' => 'Date Receipt',        'csv' => 'Date_receipt'],
                'delivered'      => ['label' => 'Delivered',           'csv' => 'Delivered_by', 'sql' => 'p1.initial'],
                'received'       => ['label' => 'Received',            'csv' => 'Received_by',  'sql' => 'p2.initial'],
                'sample_type'    => ['label' => 'Sample Type',         'csv' => 'Sample_type'],
            ],
            'order' => [['date_receipt', 'desc'], ['barcode_sample', 'asc']],
        ],
    ],

    'o2a_sample_logging' => [
        'title' => 'Sample Logging',
        'table' => 'obj2a_samplelog',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'id_samplelog'    => ['label' => 'ID',                 'csv' => 'ID'],
            'date_collection' => ['label' => 'Date Collection',    'csv' => 'Date_collection'],
            'initial'         => ['label' => 'Lab Tech',           'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'bar_samplebag'   => ['label' => 'Barcode Sample Bag', 'csv' => 'Barcode_samplebag'],
            'bar_eclosion'    => ['label' => 'Barcode Eclosion',   'csv' => 'Barcode_eclosion'],
        ],
        'order' => [['date_collection', 'desc'], ['id_samplelog', 'asc']],
    ],

    'o2a_mosquito_identifications' => [
        'title' => 'Mosquito Identifications',
        'table' => 'obj2a_identification',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'bar_storagebag' => ['label' => 'Barcode Storage Bag', 'csv' => 'Barcode_storagebag', 'class' => 'fw-semibold'],
            'initial'        => ['label' => 'Lab Tech',            'csv' => 'Initial_Person', 'sql' => 'p.initial'],
            'date_ident'     => ['label' => 'Date Identification', 'csv' => 'Date_identification'],
            'catch_met'      => ['label' => 'Catch Method',        'csv' => 'Catch_methode'],
            'no_mosquito'    => ['label' => '# Mosquitos',         'csv' => 'SumMosquito'],

            // CSV only (the species are shown in the child rows of the table)
            'aedes_aegypt_male'          => ['csv' => 'Aedes_aegypt_male',          'export_only' => true],
            'aedes_aegypt_female'        => ['csv' => 'Aedes_aegypt_female',        'export_only' => true],
            'aedes_albopictus_male'      => ['csv' => 'Aedes_albopictus_male',      'export_only' => true],
            'aedes_albopictus_female'    => ['csv' => 'Aedes_albopictus_female',    'export_only' => true],
            'aedes_polynesiensis_male'   => ['csv' => 'Aedes_polynesiensis_male',   'export_only' => true],
            'aedes_polynesiensis_female' => ['csv' => 'Aedes_polynesiensis_female', 'export_only' => true],
            'aedes_other_male'           => ['csv' => 'Aedes_other_male',           'export_only' => true],
            'aedes_other_female'         => ['csv' => 'Aedes_other_female',         'export_only' => true],
            'culex_male'                 => ['csv' => 'Culex_male',                 'export_only' => true],
            'culex_female'               => ['csv' => 'Culex_female',               'export_only' => true],
            'culex_sitiens_male'         => ['csv' => 'Culex_sitiens_male',         'export_only' => true],
            'culex_sitiens_female'       => ['csv' => 'Culex_sitiens_female',       'export_only' => true],
            'culexann_male'              => ['csv' => 'Culexann_male',              'export_only' => true],
            'culexann_female'            => ['csv' => 'Culexann_female',            'export_only' => true],
            'culex_other_male'           => ['csv' => 'Culex_other_male',           'export_only' => true],
            'culex_other_female'         => ['csv' => 'Culex_other_female',         'export_only' => true],
            'anopheles_male'             => ['csv' => 'Anopheles_male',             'export_only' => true],
            'anopheles_female'           => ['csv' => 'Anopheles_female',           'export_only' => true],
            'uranotaenia_male'           => ['csv' => 'Uranotaenia_male',           'export_only' => true],
            'uranotaenia_female'         => ['csv' => 'Uranotaenia_female',         'export_only' => true],
            'mansonia_male'              => ['csv' => 'Mansonia_male',              'export_only' => true],
            'mansonia_female'            => ['csv' => 'Mansonia_female',            'export_only' => true],
            'other_male'                 => ['csv' => 'Other_male',                 'export_only' => true],
            'other_female'               => ['csv' => 'Other_female',               'export_only' => true],

            // the old view wrapped these three in IFNULL(..., 0), keep the same behaviour
            'culex_larvae'               => ['csv' => 'Culex_larvae', 'export_only' => true, 'sql' => 'IFNULL(t.culex_larvae, 0)'],
            'aedes_larvae'               => ['csv' => 'Aedes_larvae', 'export_only' => true, 'sql' => 'IFNULL(t.aedes_larvae, 0)'],
            'unidentify'                 => ['csv' => 'Unidentify',   'export_only' => true, 'sql' => 'IFNULL(t.unidentify, 0)'],

            'notes'                      => ['csv' => 'Notes', 'export_only' => true],
        ],
        'order' => [['date_ident', 'desc'], ['bar_storagebag', 'asc']],

        // click "+" next to the barcode: species breakdown (only species that were found)
        'detail' => [
            'id_column' => 'bar_storagebag',
            'key'       => 't.bar_storagebag',
            'labels'    => ['Mosquito Name', 'Male', 'Female', 'Total'],
            'pivot'     => [
                ['Aedes aegypti',       'aedes_aegypt_male',        'aedes_aegypt_female'],
                ['Aedes albopictus',    'aedes_albopictus_male',    'aedes_albopictus_female'],
                ['Aedes polynesiensis', 'aedes_polynesiensis_male', 'aedes_polynesiensis_female'],
                ['Aedes other',         'aedes_other_male',         'aedes_other_female'],
                ['Culex',               'culex_male',               'culex_female'],
                ['Culex sitiens',       'culex_sitiens_male',       'culex_sitiens_female'],
                ['Culexann',            'culexann_male',            'culexann_female'],
                ['Culex other',         'culex_other_male',         'culex_other_female'],
                ['Anopheles',           'anopheles_male',           'anopheles_female'],
                ['Uranotaenia',         'uranotaenia_male',         'uranotaenia_female'],
                ['Mansonia',            'mansonia_male',            'mansonia_female'],
                ['Other',               'other_male',               'other_female'],
                ['Culex larvae',        'culex_larvae',             null],
                ['Aedes larvae',        'aedes_larvae',             null],
                ['Unidentified',        'unidentify',               null],
            ],
        ],
    ],

    // ============================================================ OBJECTIVE 2B

    'o2b_bootsocks_before' => [
        'title' => 'Bootsocks Weights (before sample collection)',
        'table' => 'obj2b_bootsocks_before',
        'columns' => [
            'barcode_bootsocks'   => ['label' => 'Barcode Bootsocks',            'csv' => 'Barcode_bootsocks', 'class' => 'fw-semibold'],
            'date_weighed'        => ['label' => 'Date Weighed',                 'csv' => 'Date_weighed'],
            'bootsock_weight_dry' => ['label' => 'Bootsocks Weighed - Dry (g)',  'csv' => 'Weight_dry'],
            'comments'            => ['label' => 'Comments',                     'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_weighed', 'desc'], ['barcode_bootsocks', 'asc']],
    ],

    'o2b_bootsocks_after' => [
        'title' => 'Bootsocks Weights (after sample collection)',
        'table' => 'obj2b_bootsocks_after',
        'columns' => [
            'barcode_bootsocks'   => ['label' => 'Barcode Bootsocks',            'csv' => 'Barcode_bootsocks', 'class' => 'fw-semibold'],
            'date_weighed'        => ['label' => 'Date Weighed',                 'csv' => 'Date_weighed'],
            'bootsock_weight_wet' => ['label' => 'Bootsocks Weighed - Wet (g)',  'csv' => 'Weight_wet'],
            'comments'            => ['label' => 'Comments',                     'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_weighed', 'desc'], ['barcode_bootsocks', 'asc']],
    ],

    'o2b_sample_reception' => [
        'title' => 'Sample Reception (2B)',
        'table' => 'obj2b_receipt',
        'joins' => [
            ['ref_sampletype as s', 's.id_sampletype', 't.id_type2b'],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode Sample',  'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_arrival'    => ['label' => 'Date Arrive',     'csv' => 'Date_arrival'],
            'time_arrival'    => ['label' => 'Time Arrive',     'csv' => 'Time_arrival', 'sql' => "DATE_FORMAT(t.time_arrival, '%H:%i')"],
            'sampletype2b'    => ['label' => 'Sample Type',     'csv' => 'Sample_type',  'sql' => 's.sampletype'],
            'png_control'     => ['label' => 'P&G Control',     'csv' => 'P&G_control'],
            'barcode_tinytag' => ['label' => 'Barcode Tinytag', 'csv' => 'Barcode_tinytag'],
            'comments'        => ['label' => 'Comments',        'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_arrival', 'desc'], ['time_arrival', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o2b_bootsocks_stomacher' => [
        'title' => 'Bootsocks - Stomacher',
        'table' => 'obj2b_bs_stomacher',
        'columns' => [
            'barcode_sample'   => ['label' => 'Barcode Sample',        'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_conduct'     => ['label' => 'Date Conducted',        'csv' => 'Date_conduct'],
            'elution_no'       => ['label' => 'Elution Number',        'csv' => 'Elution_number'],
            'barcode_bootsock' => ['label' => 'Barcode Bootsock',      'csv' => 'Barcode_bootsock'],
            'elution'          => ['label' => 'Elution',               'csv' => 'Elution'],
            'barcode_falcon'   => ['label' => 'Barcode Falcon Tube',   'csv' => 'Barcode_falcon'],
            'volume_stomacher' => ['label' => 'Volume Recovered After Stomacher (mL)', 'csv' => 'Volume'],
        ],
        'order' => [['date_conduct', 'desc'], ['barcode_sample', 'asc'], ['elution_no', 'asc']],

        // click "+" next to the barcode: Endetec and IDEXX records of that sample
        'detail' => [
            'id_column' => 'barcode_sample',
            'sections'  => [
                [
                    'title'  => 'Endetec',
                    'table'  => 'obj2b_subbs_endetec',
                    'key'    => 'd.barcode_sample',
                    'parent' => 't.barcode_sample',
                    'columns' => [
                        'barcode_endetec' => ['label' => 'Barcode Endetec'],
                        'barcode_falcon1' => ['label' => 'Barcode Falcon1'],
                        'volume_falcon1'  => ['label' => 'Volume Falcon1'],
                        'barcode_falcon2' => ['label' => 'Barcode Falcon2'],
                        'volume_falcon2'  => ['label' => 'Volume Falcon2'],
                        'dilution'        => ['label' => 'Dilution Factor'],
                        'time_incubation' => ['label' => 'Time Inc. Started', 'sql' => "DATE_FORMAT(d.time_incubation, '%H:%i')"],
                        'comments'        => ['label' => 'Comments'],
                    ],
                    'order' => 'd.barcode_endetec',
                ],
                [
                    'title'  => 'IDEXX',
                    'table'  => 'obj2b_subbs_idexx',
                    'key'    => 'd.barcode_sample',
                    'parent' => 't.barcode_sample',
                    'columns' => [
                        'barcode_colilert' => ['label' => 'Barcode Colilert'],
                        'barcode_falcon1'  => ['label' => 'Barcode Falcon1'],
                        'volume_falcon1'   => ['label' => 'Volume Falcon1 (mL)'],
                        'barcode_falcon2'  => ['label' => 'Barcode Falcon2'],
                        'volume_falcon2'   => ['label' => 'Volume Falcon2 (mL)'],
                        'dilution'         => ['label' => 'Dilution'],
                        'time_incubation'  => ['label' => 'Time Inc. Started', 'sql' => "DATE_FORMAT(d.time_incubation, '%H:%i')"],
                        'comments'         => ['label' => 'Comments'],
                    ],
                    'order' => 'd.barcode_colilert',
                ],
            ],
        ],

        // wide CSV, same layout as the old file: one line per sample with the Micro1/Micro2/Moisture1/Moisture2 groups
        'export' => [
            'table' => 'obj2b_receipt',
            'where' => ['t.id_type2b = 9'],
            'joins' => [
                ['obj2b_bs_stomacher as g', 'g.barcode_sample', 't.barcode_sample', ["g.elution_no = 'Micro1'",    'g.lab = t.lab', 'g.flag = 0']],
                ['obj2b_bs_stomacher as o', 'o.barcode_sample', 't.barcode_sample', ["o.elution_no = 'Micro2'",    'o.lab = t.lab', 'o.flag = 0']],
                ['obj2b_bs_stomacher as s', 's.barcode_sample', 't.barcode_sample', ["s.elution_no = 'Moisture1'", 's.lab = t.lab', 's.flag = 0']],
                ['obj2b_bs_stomacher as m', 'm.barcode_sample', 't.barcode_sample', ["m.elution_no = 'Moisture2'", 'm.lab = t.lab', 'm.flag = 0']],
                ['obj2b_subbs_endetec as h', 'h.barcode_sample', 't.barcode_sample', ['h.lab = t.lab', 'h.flag = 0']],
                // IDEXX rows of the sample itself or of its bootsocks (as in the old query)
                ['obj2b_subbs_idexx as j', 'j.lab', 't.lab', [
                    'j.flag = 0',
                    'g.barcode_sample IS NOT NULL',
                    '(j.barcode_sample = t.barcode_sample OR j.barcode_sample IN (SELECT st.barcode_bootsock FROM obj2b_bs_stomacher st WHERE st.barcode_sample = t.barcode_sample AND st.lab = t.lab AND st.flag = 0))',
                ]],
            ],
            'columns' => [
                'barcode_sample' => ['csv' => 'Barcode_sample'],
                'date_conduct'   => ['csv' => 'Date_conduct',          'sql' => 'g.date_conduct'],
                'bootsocks1'     => ['csv' => 'Barcode_bootsocks1',    'sql' => 'g.barcode_bootsock'],
                'mi1_no'         => ['csv' => 'Elution_number_Micro1', 'sql' => 'g.elution_no'],
                'mi1_elution'    => ['csv' => 'Elution_Micro1',        'sql' => 'g.elution'],
                'mi1_comment'    => ['csv' => 'Elution_Micro1_comment', 'sql' => 'g.elu_comments'],
                'mi1_falcon'     => ['csv' => 'Barcode_falcon_Micro1', 'sql' => 'g.barcode_falcon'],
                'mi1_volume'     => ['csv' => 'Volume_Micro1',         'sql' => 'g.volume_stomacher'],
                'mi2_no'         => ['csv' => 'Elution_number_Micro2', 'sql' => 'o.elution_no'],
                'mi2_elution'    => ['csv' => 'Elution_Micro2',        'sql' => 'o.elution'],
                'mi2_comment'    => ['csv' => 'Elution_Micro2_comment', 'sql' => 'o.elu_comments'],
                'mi2_falcon'     => ['csv' => 'Barcode_falcon_Micro2', 'sql' => 'o.barcode_falcon'],
                'mi2_volume'     => ['csv' => 'Volume_Micro2',         'sql' => 'o.volume_stomacher'],
                'bootsocks2'     => ['csv' => 'Barcode_bootsocks2',    'sql' => 's.barcode_bootsock'],
                'mo1_no'         => ['csv' => 'Elution_number_Moisture1', 'sql' => 's.elution_no'],
                'mo1_elution'    => ['csv' => 'Elution_Moisture1',     'sql' => 's.elution'],
                'mo1_comment'    => ['csv' => 'Elution_Moisture1_comment', 'sql' => 's.elu_comments'],
                'mo1_falcon'     => ['csv' => 'Barcode_falcon_Moisture1', 'sql' => 's.barcode_falcon'],
                'mo1_volume'     => ['csv' => 'Volume_Moisture1',      'sql' => 's.volume_stomacher'],
                'mo2_no'         => ['csv' => 'Elution_number_Moisture2', 'sql' => 'm.elution_no'],
                'mo2_elution'    => ['csv' => 'Elution_Moisture2',     'sql' => 'm.elution'],
                'mo2_comment'    => ['csv' => 'Elution_Moisture2_comment', 'sql' => 'm.elu_comments'],
                'mo2_falcon'     => ['csv' => 'Barcode_falcon_Moisture2', 'sql' => 'm.barcode_falcon'],
                'mo2_volume'     => ['csv' => 'Volume_Moisture2',      'sql' => 'm.volume_stomacher'],

                'e_barcode'      => ['csv' => 'Endetec_barcode_endetec', 'sql' => 'h.barcode_endetec'],
                'e_falcon1'      => ['csv' => 'Endetec_barcode_falcon1', 'sql' => 'h.barcode_falcon1'],
                'e_volume1'      => ['csv' => 'Endetec_volume_falcon1',  'sql' => 'h.volume_falcon1'],
                'e_falcon2'      => ['csv' => 'Endetec_barcode_falcon2', 'sql' => 'h.barcode_falcon2'],
                'e_volume2'      => ['csv' => 'Endetec_volume_falcon2',  'sql' => 'h.volume_falcon2'],
                'e_dilution'     => ['csv' => 'Endetec_dilution',        'sql' => 'h.dilution'],
                'e_time'         => ['csv' => 'Endetec_time_incu_start', 'sql' => 'h.time_incubation'],
                'e_comments'     => ['csv' => 'Endetec_comments',        'sql' => 'h.comments'],

                'i_barcode'      => ['csv' => 'Idexx_barcode_colilert',  'sql' => 'j.barcode_colilert'],
                'i_falcon1'      => ['csv' => 'Idexx_barcode_falcon1',   'sql' => 'j.barcode_falcon1'],
                'i_volume1'      => ['csv' => 'Idexx_volume_falcon1',    'sql' => 'j.volume_falcon1'],
                'i_falcon2'      => ['csv' => 'Idexx_barcode_falcon2',   'sql' => 'j.barcode_falcon2'],
                'i_volume2'      => ['csv' => 'Idexx_volume_falcon2',    'sql' => 'j.volume_falcon2'],
                'i_dilution'     => ['csv' => 'Idexx_dilution',          'sql' => 'j.dilution'],
                'i_time'         => ['csv' => 'Idexx_time_incu_start',   'sql' => 'j.time_incubation'],
                'i_comments'     => ['csv' => 'Idexx_comments',          'sql' => 'j.comments'],
            ],
            'order' => [['barcode_sample', 'asc']],
        ],
    ],

    'o2b_sample_prep' => [
        'title' => 'Sediment - Sample Prep',
        'table' => 'obj2b_sediment_prep',
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode Sample',         'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_conduct'   => ['label' => 'Date Conducted',         'csv' => 'Date_conduct'],
            'elution'        => ['label' => 'Elution',                'csv' => 'Elution'],
            'elu_comments'   => ['label' => 'Elution Comments',       'csv' => 'Elution_comments', 'class' => 'col-trunc'],
            'barcode_tube'   => ['label' => 'Barcode of Tube',        'csv' => 'Barcode_tube'],
            'subsample_wet'  => ['label' => 'Sample Wet Weight (g)',  'csv' => 'Subsample_wet_weight'],
        ],
        'order' => [['date_conduct', 'desc'], ['barcode_sample', 'asc']],

        'detail' => [
            'id_column' => 'barcode_sample',
            'sections'  => [
                [
                    'title'  => 'Endetec',
                    'table'  => 'obj2b_subsd_endetec',
                    'key'    => 'd.barcode_sample',
                    'parent' => 't.barcode_sample',
                    'columns' => [
                        'barcode_endetec' => ['label' => 'Barcode Endetec'],
                        'volume_falcon'   => ['label' => 'Volume from Falcon into Endetec (mL)'],
                        'dilution'        => ['label' => 'Dilution Factor'],
                        'time_incubation' => ['label' => 'Time Incubation Started', 'sql' => "DATE_FORMAT(d.time_incubation, '%H:%i')"],
                        'comments'        => ['label' => 'Comments'],
                    ],
                    'order' => 'd.barcode_endetec',
                ],
                [
                    'title'  => 'IDEXX',
                    'table'  => 'obj2b_subsd_idexx',
                    'key'    => 'd.barcode_sample',
                    'parent' => 't.barcode_sample',
                    'columns' => [
                        'barcode_colilert' => ['label' => 'Barcode Colilert'],
                        'volume'           => ['label' => 'Volume (mL) Added in Bottle'],
                        'dilution'         => ['label' => 'Dilution'],
                        'time_incubation'  => ['label' => 'Time Incubation Started', 'sql' => "DATE_FORMAT(d.time_incubation, '%H:%i')"],
                        'comments'         => ['label' => 'Comments'],
                    ],
                    'order' => 'd.barcode_colilert',
                ],
            ],
        ],

        // the CSV joins the Endetec and IDEXX records of the same sample (same headers as the old file)
        'export' => [
            'table' => 'obj2b_sediment_prep',
            'joins' => [
                ['obj2b_subsd_endetec as e', 'e.barcode_sample', 't.barcode_sample', ['e.lab = t.lab', 'e.flag = 0']],
                ['obj2b_subsd_idexx as i',   'i.barcode_sample', 't.barcode_sample', ['i.lab = t.lab', 'i.flag = 0']],
            ],
            'columns' => [
                'barcode_sample' => ['csv' => 'Barcode_sample'],
                'date_conduct'   => ['csv' => 'Date_conduct'],
                'elution'        => ['csv' => 'Elution'],
                'elu_comments'   => ['csv' => 'Elution_comments'],
                'barcode_tube'   => ['csv' => 'Barcode_tube'],
                'subsample_wet'  => ['csv' => 'Subsample_wet_weight'],
                'e_barcode'      => ['csv' => 'Barcode_endetec',          'sql' => 'e.barcode_endetec'],
                'e_volume'       => ['csv' => 'Volume_falcon',            'sql' => 'e.volume_falcon'],
                'e_dilution'     => ['csv' => 'Dilution_endetec',         'sql' => 'e.dilution'],
                'e_time'         => ['csv' => 'Time_incubation_endetec',  'sql' => "DATE_FORMAT(e.time_incubation, '%H:%i')"],
                'e_comments'     => ['csv' => 'Comments_endetec',         'sql' => 'e.comments'],
                'i_barcode'      => ['csv' => 'Barcode_colilert',         'sql' => 'i.barcode_colilert'],
                'i_volume'       => ['csv' => 'Volume_IDEXX',             'sql' => 'i.volume'],
                'i_dilution'     => ['csv' => 'Dilution_IDEXX',           'sql' => 'i.dilution'],
                'i_time'         => ['csv' => 'Time_incubation_IDEXX',    'sql' => "DATE_FORMAT(i.time_incubation, '%H:%i')"],
                'i_comments'     => ['csv' => 'Comments_IDEXX',           'sql' => 'i.comments'],
            ],
            'order' => [['date_conduct', 'desc'], ['barcode_sample', 'asc']],
        ],
    ],

    'o2b_other_lab' => [
        'title' => 'Other Lab Analysis',
        'table' => 'obj2b_chemistry',
        'joins' => [
            ['ref_sampletype as s', 's.id_sampletype', 't.id_type2bwat'],
        ],
        'columns' => [
            'barcode_sample'        => ['label' => 'Barcode Sample', 'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'sampletype2bwat'       => ['label' => 'Water Type',     'csv' => 'Sample_watertype', 'sql' => 's.sampletype'],
            'barcode_nitro'         => ['label' => 'BTKL chems', 'labels' => [1 => 'BTKL chems', 2 => 'WAF chems']],
            'barcode_nitro2'        => ['label' => 'BBLK chems', 'labels' => [1 => 'BBLK chems', 2 => 'Other chems']],
            'barcode_microbiology'  => ['label' => 'BTKL micro', 'labels' => [1 => 'BTKL micro', 2 => 'WAF micro']],
            'barcode_microbiology2' => ['label' => 'BBLK micro', 'labels' => [1 => 'BBLK micro', 2 => 'Other micro']],
            'barcode_rise_lab'      => ['label' => 'RISE Lab'],
            'comments'              => ['label' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['barcode_sample', 'asc']],

        // CSV column order and headers follow the old file (headers depend on the selected lab)
        'export' => [
            'table' => 'obj2b_chemistry',
            'joins' => [
                ['ref_sampletype as s', 's.id_sampletype', 't.id_type2bwat'],
            ],
            'columns' => [
                'barcode_sample'        => ['csv' => 'Barcode_sample'],
                'sampletype2bwat'       => ['csv' => 'Sample_watertype', 'sql' => 's.sampletype'],
                'barcode_rise_lab'      => ['csv' => 'RISE_Lab_Barcode'],
                'barcode_nitro'         => ['csvs' => [1 => 'BTKL_chems_Barcode',  2 => 'WAF_chems_Barcode']],
                'deliver_1'             => ['csvs' => [1 => 'BTKL_chems_Deliver',  2 => 'WAF_chems_Deliver'],  'sql' => 't.`3rdparty_lab`'],
                'barcode_nitro2'        => ['csvs' => [1 => 'BBLK_chems_Barcode',  2 => 'Other_chems_Barcode']],
                'deliver_2'             => ['csvs' => [1 => 'BBLK_chems_Deliver',  2 => 'Other_chems_Deliver'], 'sql' => 't.`3rdparty_lab2`'],
                'barcode_microbiology'  => ['csvs' => [1 => 'BTKL_micro_Barcode',  2 => 'WAF_micro_Barcode']],
                'deliver_3'             => ['csvs' => [1 => 'BTKL_micro_Deliver',  2 => 'WAF_micro_Deliver'],  'sql' => 't.`3rdparty_lab3`'],
                'barcode_microbiology2' => ['csvs' => [1 => 'BBLK_micro_Barcode',  2 => 'Other_micro_Barcode']],
                'deliver_4'             => ['csvs' => [1 => 'BBLK_micro_Deliver',  2 => 'Other_micro_Deliver'], 'sql' => 't.`3rdparty_lab4`'],
                'comments'              => ['csv' => 'Comments'],
            ],
            'order' => [['barcode_sample', 'asc']],
        ],
    ],

    'o2b_blank_in' => [
        'title' => 'Blank - In (Endetec/IDEXX)',
        'table' => 'obj2b_blanks',
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode Sample',        'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_conduct'    => ['label' => 'Date Conducted',        'csv' => 'Date_conduct'],
            'time_incubation' => ['label' => 'Time Incubation Start', 'csv' => 'Time_incubation', 'sql' => "DATE_FORMAT(t.time_incubation, '%H:%i')"],
            'blank_type'      => ['label' => 'Blank Type',            'csv' => 'Blank_type'],
            'barcode_endidx'  => ['label' => 'Barcode Endetec/IDEXX', 'csv' => 'barcode_end-idx'],
            'volume'          => ['label' => 'Volume (mL) Added',     'csv' => 'Volume'],
            'dilution'        => ['label' => 'Dilution',              'csv' => 'Dilution'],
            'comments'        => ['label' => 'Comments',              'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_conduct', 'desc'], ['time_incubation', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o2b_endetec_in' => [
        'title' => 'Endetec - In (Water)',
        'table' => 'obj2b_endetec1',
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode Sample',        'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_conduct'    => ['label' => 'Date Conducted',        'csv' => 'Date_conduct'],
            'time_incubation' => ['label' => 'Time Incubation Start', 'csv' => 'Time_incubation', 'sql' => "DATE_FORMAT(t.time_incubation, '%H:%i')"],
            'barcode_endetec' => ['label' => 'Barcode Endetec',       'csv' => 'Barcode_endetec'],
            'volume'          => ['label' => 'Volume (mL) Added',     'csv' => 'Volume'],
            'dilution'        => ['label' => 'Dilution',              'csv' => 'Dilution'],
            'comments'        => ['label' => 'Comments',              'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_conduct', 'desc'], ['time_incubation', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o2b_endetec_out_w' => [
        'title' => 'Endetec - Out (Water)',
        'table' => 'obj2b_endetec2',
        'columns' => [
            'barcode_endetec' => ['label' => 'Barcode Endetec',                  'csv' => 'Barcode_endetec', 'class' => 'fw-semibold'],
            'date_conduct'    => ['label' => 'Date Conducted',                   'csv' => 'Date_conduct'],
            'time_ecoli'      => ['label' => 'Time E. coli Detection (Duration)', 'csv' => 'Time_E.coli_detection', 'sql' => "DATE_FORMAT(t.time_ecoli, '%H:%i')"],
            'volume_ecoli'    => ['label' => 'E. coli Volume Added',            'csv' => 'E.coli_CFU/volume_added'],
            'ecoli_cfu'       => ['label' => 'E. coli CFU/100mL',               'csv' => 'E.coli_CFU/100mL'],
            'total_coliforms' => ['label' => 'Coliform Volume Added',           'csv' => 'Total_coliforms_CFU/volume_added'],
            'total_coli_cfu'  => ['label' => 'Coliform CFU/100mL',              'csv' => 'Total_coliforms_CFU/100mL'],
            'comments'        => ['label' => 'Comments',                        'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_conduct', 'desc'], ['barcode_endetec', 'asc']],
    ],

    'o2b_endetec_out_bs' => [
        'title' => 'Endetec - Out (Bootsocks, Sediment)',
        'table' => 'obj2b_endetec3',
        'columns' => [
            'barcode_endetec' => ['label' => 'Barcode Endetec',                   'csv' => 'Barcode_endetec', 'class' => 'fw-semibold'],
            'date_conduct'    => ['label' => 'Date Conducted',                    'csv' => 'Date_conduct'],
            'time_ecoli'      => ['label' => 'Time to E. coli Detection (Duration)', 'csv' => 'Time_E.coli_detection', 'sql' => "DATE_FORMAT(t.time_ecoli, '%H:%i')"],
            'volume_ecoli'    => ['label' => 'E. coli CFU/Volume Added',          'csv' => 'E.coli_CFU/volume_added'],
            'total_coliforms' => ['label' => 'Total Coliforms CFU/Volume Added',  'csv' => 'Total_coliforms_CFU/volume_added'],
            'comments'        => ['label' => 'Comments',                          'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_conduct', 'desc'], ['barcode_endetec', 'asc']],
    ],

    'o2b_idexx_in' => [
        'title' => 'IDEXX - In (Water)',
        'table' => 'obj2b_idexx1',
        'columns' => [
            'barcode_sample'    => ['label' => 'Barcode Sample',     'class' => 'fw-semibold'],
            'date_conduct'      => ['label' => 'Date Conducted'],
            'barcode_colilert'  => ['label' => 'Barcode Colilert'],
            'volume'            => ['label' => 'Volume (mL)'],
            'barcode_colilert2' => ['label' => 'Barcode Colilert_2'],
            'volume2'           => ['label' => 'Volume_2 (mL)'],
            'comments'          => ['label' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_conduct', 'desc'], ['barcode_sample', 'asc']],

        // the CSV has all 11 columns, in the order of the old file
        'export' => [
            'columns' => [
                'barcode_sample'    => ['csv' => 'Barcode_sample'],
                'date_conduct'      => ['csv' => 'Date_conduct'],
                'time_incubation'   => ['csv' => 'Time_incubation_started', 'sql' => "DATE_FORMAT(t.time_incubation, '%H:%i')"],
                'barcode_colilert'  => ['csv' => 'Barcode_colilert'],
                'volume'            => ['csv' => 'Volume(mL)added_in_bottle'],
                'dilution'          => ['csv' => 'Dilution'],
                'comments'          => ['csv' => 'Comments'],
                'barcode_colilert2' => ['csv' => 'Barcode_colilert_2'],
                'volume2'           => ['csv' => 'Volume_2(mL)added_in_bottle'],
                'dilution2'         => ['csv' => 'Dilution_2'],
                'comments2'         => ['csv' => 'Comments_2'],
            ],
            'order' => [['date_conduct', 'desc'], ['barcode_sample', 'asc']],
        ],
    ],

    'o2b_idexx_out' => [
        'title' => 'IDEXX - Out (All)',
        'table' => 'obj2b_idexx2',
        'columns' => [
            'barcode_colilert'     => ['label' => 'Barcode Colilert',            'csv' => 'Barcode_colilert', 'class' => 'fw-semibold'],
            'date_conduct'         => ['label' => 'Date Conducted',              'csv' => 'Date_conduct'],
            'timeout_incubation'   => ['csv' => 'Time_incubation_finished', 'sql' => "DATE_FORMAT(t.timeout_incubation, '%H:%i')", 'export_only' => true],
            'time_minutes'         => ['label' => 'Incubation Duration (minutes)', 'csv' => 'Time_incubation_duration'],
            'ecoli_largewells'     => ['label' => 'E. coli Large Wells',         'csv' => 'E.coli_large_wells'],
            'ecoli_smallwells'     => ['label' => 'E. coli Small Wells',         'csv' => 'E.coli_small_wells'],
            'ecoli_mpn'            => ['label' => 'E. coli MPN/100 mL',          'csv' => 'E.coli_MPN/100mL'],
            'coliforms_largewells' => ['label' => 'Coliform Large Wells',        'csv' => 'Coliforms_large_wells'],
            'coliforms_smallwells' => ['label' => 'Coliform Small Wells',        'csv' => 'Coliforms_small_wells'],
            'coliforms_mpn'        => ['label' => 'Coliform MPN/100 mL',         'csv' => 'Total_coliforms_MPN/100mL'],
            'comments'             => ['csv' => 'Comments', 'export_only' => true],
        ],
        'order' => [['date_conduct', 'desc'], ['barcode_colilert', 'asc']],
    ],

    'o2b_macconkey_in' => [
        'title' => 'Macconkey In',
        'table' => 'obj2b_mac1',
        'joins' => [['ref_person as p', 'p.id_person', 't.id_person']],
        'columns' => [
            'bar_macconkey'   => ['label' => 'Barcode Macconkey', 'csv' => 'Barcode_macconkey', 'class' => 'fw-semibold'],
            'barcode_sample'  => ['label' => 'Barcode Sample',    'csv' => 'Barcode_sample/falcon1'],
            'barcode_falcon2' => ['csv' => 'Barcode_falcon2', 'export_only' => true],
            'date_process'    => ['label' => 'Date Process',      'csv' => 'Date_process'],
            'time_process'    => ['label' => 'Time Process',      'csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
            'initial'         => ['label' => 'Person',            'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'volume'          => ['label' => 'Volume (mL)',       'csv' => 'Volume(mL)'],
            'comments'        => ['label' => 'Comments',          'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['bar_macconkey', 'asc']],
    ],

    'o2b_macconkey_out' => [
        'title' => 'Macconkey Out',
        'table' => 'obj2b_mac2',
        'joins' => [
            ['ref_person as p',       'p.id_person',       't.id_person'],
            ['freezer_in as f1',      'f1.id',             't.id_freezer1',      ['f1.lab = t.lab']],
            ['ref_location_80 as l1', 'l1.id_location_80', 'f1.id_location_80',  ['l1.lab = t.lab']],
            ['freezer_in as f2',      'f2.id',             't.id_freezer2',      ['f2.lab = t.lab']],
            ['ref_location_80 as l2', 'l2.id_location_80', 'f2.id_location_80',  ['l2.lab = t.lab']],
        ],
        'columns' => [
            'bar_macconkey' => ['label' => 'Barcode Macconkey', 'csv' => 'Barcode_macconkey', 'class' => 'fw-semibold'],
            'date_process'  => ['label' => 'Date Process',      'csv' => 'Date_process'],
            'time_process'  => ['label' => 'Time Process',      'csv' => 'Time_process', 'sql' => "DATE_FORMAT(t.time_process, '%H:%i')"],
            'initial'       => ['label' => 'Lab Tech',          'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'bar_macsweep1' => ['label' => 'Macconkey Sweep-1', 'csv' => 'Barcode_mac_sweep1'],
            'cryobox1'      => ['label' => 'Cryobox-1',         'csv' => 'Cryobox1'],
            'location1'     => ['csv' => 'Location1', 'sql' => "CONCAT('F', l1.freezer, '-', 'S', l1.shelf, '-', 'R', l1.rack, '-', 'DRW', l1.rack_level)", 'export_only' => true],
            'bar_macsweep2' => ['label' => 'Macconkey Sweep-2', 'csv' => 'Barcode_mac_sweep2'],
            'cryobox2'      => ['label' => 'Cryobox-2',         'csv' => 'Cryobox2'],
            'location2'     => ['csv' => 'Location2', 'sql' => "CONCAT('F', l2.freezer, '-', 'S', l2.shelf, '-', 'R', l2.rack, '-', 'DRW', l2.rack_level)", 'export_only' => true],
            'comments'      => ['label' => 'Comments',          'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['time_process', 'desc'], ['bar_macconkey', 'asc']],
    ],

    'o2b_metagenomics_wb' => [
        'title' => 'Metagenomics (Water & Bootsocks)',
        'table' => 'obj2b_metagenomics',
        'joins' => [
            ['freezer_in as f',      'f.id',             't.id_freezer',     ['f.lab = t.lab']],
            ['ref_location_80 as l', 'l.id_location_80', 'f.id_location_80', ['l.lab = t.lab']],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode (W0/S1)',             'csv' => 'Barcode_(W0/S1)', 'class' => 'fw-semibold'],
            'barcode_falcon2' => ['label' => 'Barcode Falcon 2',            'csv' => 'Barcode_falcon2'],
            'date_conduct'    => ['label' => 'Date Conducted',              'csv' => 'Date_conduct'],
            'volume_filtered' => ['label' => 'Volume Filtered (mL)',        'csv' => 'Volume_filtered'],
            'time_started'    => ['label' => 'Time Started Filtering',      'csv' => 'Time_started',  'sql' => "DATE_FORMAT(t.time_started, '%H:%i')"],
            'time_finished'   => ['label' => 'Time Finished Filtering',     'csv' => 'Time_finished', 'sql' => "DATE_FORMAT(t.time_finished, '%H:%i')"],
            'time_minutes'    => ['label' => 'Duration Filtering (minutes)', 'csv' => 'Duration(minutes)'],
            'barcode_dna_bag' => ['label' => 'Barcode DNA Storage',         'csv' => 'Barcode_DNA_bag'],
            'barcode_storage' => ['label' => 'Barcode Storage Box',         'csv' => 'Barcode_storage'],
            'location'        => ['label' => 'Freezer Location',            'csv' => 'Location', 'sql' => "CONCAT('F', l.freezer, '-', 'S', l.shelf, '-', 'R', l.rack, '-', 'DRW', l.rack_level)"],
            'comments'        => ['label' => 'Comments',                    'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_conduct', 'desc'], ['time_started', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o2b_metagenomics_sf' => [
        'title' => 'Metagenomics (Sediment & Feces)',
        'table' => 'obj2b_meta_sediment',
        'joins' => [
            ['freezer_in as f1',      'f1.id',             't.id_freezer1',      ['f1.lab = t.lab']],
            ['ref_location_80 as l1', 'l1.id_location_80', 'f1.id_location_80',  ['l1.lab = t.lab']],
            ['freezer_in as f2',      'f2.id',             't.id_freezer2',      ['f2.lab = t.lab']],
            ['ref_location_80 as l2', 'l2.id_location_80', 'f2.id_location_80',  ['l2.lab = t.lab']],
        ],
        'columns' => [
            'barcode_sample'   => ['label' => 'Barcode Sample',    'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_conduct'     => ['label' => 'Date Conducted',    'csv' => 'Date_conduct'],
            'barcode_dna1'     => ['label' => 'Barcode DNA Tube1', 'csv' => 'Barcode_dna_tube1'],
            'weight_sub1'      => ['csv' => 'Weight_tube1', 'export_only' => true],
            'barcode_storage1' => ['label' => 'Barcode BOX Tube1', 'csv' => 'Barcode_box1'],
            'position_tube1'   => ['label' => 'Position Tube1',    'csv' => 'Position_tube1'],
            'location1'        => ['csv' => 'Location_tube1', 'sql' => "CONCAT('F', l1.freezer, '-', 'S', l1.shelf, '-', 'R', l1.rack, '-', 'DRW', l1.rack_level)", 'export_only' => true],
            'barcode_dna2'     => ['label' => 'Barcode DNA Tube2', 'csv' => 'Barcode_dna_tube2'],
            'weight_sub2'      => ['csv' => 'Weight_tube2', 'export_only' => true],
            'barcode_storage2' => ['label' => 'Barcode BOX Tube2', 'csv' => 'Barcode_box2'],
            'position_tube2'   => ['label' => 'Position Tube2',    'csv' => 'Position_tube2'],
            'location2'        => ['csv' => 'Location_tube2', 'sql' => "CONCAT('F', l2.freezer, '-', 'S', l2.shelf, '-', 'R', l2.rack, '-', 'DRW', l2.rack_level)", 'export_only' => true],
            'comments'         => ['label' => 'Comments',          'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_conduct', 'desc'], ['barcode_sample', 'asc']],
    ],    

    'o2b_moisture_initial' => [
        'title' => 'Moisture - Initial',
        'table' => 'obj2b_moisture1',
        'columns' => [
            'barcode_sample'     => ['label' => 'Barcode (D0/S1)',        'csv' => 'Barcode_(D0/S1)', 'class' => 'fw-semibold'],
            'barcode_falcon2'    => ['label' => 'Barcode Falcon 2',       'csv' => 'Barcode_falcon2'],
            'date_moisture'      => ['label' => 'Date Moisture',          'csv' => 'Date_moisture'],
            'barcode_bootsock'   => ['csv' => 'Barcode_bootsock', 'export_only' => true],
            'barcode_foil'       => ['label' => 'Barcode Foil/Tray',      'csv' => 'Barcode_foil'],
            'foil_weight'        => ['label' => 'Foil/Tray Weight (g)',   'csv' => 'Foil_weight(g)'],
            'time_filter_start'  => ['label' => 'Time Started Filtering', 'csv' => 'Time_start_filter',  'sql' => "DATE_FORMAT(t.time_filter_start, '%H:%i')"],
            'time_filter_finish' => ['label' => 'Time Finished Filtering', 'csv' => 'Time_finish_filter', 'sql' => "DATE_FORMAT(t.time_filter_finish, '%H:%i')"],
            'wet_weight'         => ['label' => 'Wet Weight Foil+Sample (g)', 'csv' => 'Wet_weight(foil+sample)'],
            'time_incubator'     => ['label' => 'Time in Incubator',      'csv' => 'Time_incubator', 'sql' => "DATE_FORMAT(t.time_incubator, '%H:%i')"],
            'comments'           => ['label' => 'Comments',               'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_moisture', 'desc'], ['barcode_sample', 'asc']],
    ],

    'o2b_moisture_24' => [
        'title' => 'Moisture - 24h',
        'table' => 'obj2b_moisture2',
        'columns' => [
            'barcode_foil'  => ['label' => 'Barcode Foil',       'csv' => 'Barcode_foil', 'class' => 'fw-semibold'],
            'date_moisture' => ['label' => 'Date Moisture',      'csv' => 'Date_moisture'],
            'dry_weight24'  => ['label' => 'Dry Weight 24h (g)', 'csv' => 'Dry_weight(24h)'],
            'comments'      => ['label' => 'Comments',           'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_moisture', 'desc'], ['barcode_foil', 'asc']],
    ],

    'o2b_moisture_48' => [
        'title' => 'Moisture - 48h',
        'table' => 'obj2b_moisture3',
        'columns' => [
            'barcode_foil'  => ['label' => 'Barcode Foil',           'csv' => 'Barcode_foil', 'class' => 'fw-semibold'],
            'date_moisture' => ['label' => 'Date Moisture',          'csv' => 'Date_moisture'],
            'dry_weight48'  => ['label' => 'Dry Weight 48h (g)',     'csv' => 'Dry_weight(48h)'],
            'difference'    => ['label' => 'Difference 24 and 48h',  'csv' => 'Difference(24n48)'],
            'comments'      => ['label' => 'Comments',               'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_moisture', 'desc'], ['barcode_foil', 'asc']],
    ],

    'o2b_moisture_72' => [
        'title' => 'Moisture - 72h',
        'table' => 'obj2b_moisture4',
        'columns' => [
            'barcode_foil'  => ['label' => 'Barcode Foil',       'csv' => 'Barcode_foil', 'class' => 'fw-semibold'],
            'date_moisture' => ['label' => 'Date Moisture',      'csv' => 'Date_moisture'],
            'dry_weight72'  => ['label' => 'Dry Weight 72h (g)', 'csv' => 'Dry_weight(72h)'],
            'comments'      => ['label' => 'Comments',           'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_moisture', 'desc'], ['barcode_foil', 'asc']],
    ],    

    // ============================================================ WATER MODULE

    'wat_water_chemistry' => [
        'title' => 'Water Chemistry',
        'table' => 'obj2b_chemistry_lab',
        'joins' => [
            [$waterSub, 'c.barcode', 't.barcode_sample'],
            ['ref_sampletype as b', 'b.id_sampletype', 'c.id_type2bwat'],
            ['obj2b_receipt as d',  'd.barcode_sample', 'c.barcode_sample', ['d.lab = t.lab']],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode Sample', 'csv' => 'Barcode Sample', 'class' => 'fw-semibold'],
            'date_process'    => ['label' => 'Date Process',   'csv' => 'Date process',   'sql' => 'd.date_arrival'],
            'water_lab'       => ['label' => 'Laboratory',     'csv' => 'Laboratory',     'sql' => 'c.lab'],
            'parent_barcode'  => ['label' => 'Parent Barcode', 'csv' => 'Parent barcode', 'sql' => 'c.barcode_sample'],
            'sampletype2bwat' => ['label' => 'Water Type',     'csv' => 'Water type',     'sql' => 'b.sampletype'],
            'ammonia'         => ['label' => 'Ammonia',        'csv' => 'Ammonia (NH3-N) mg/L'],
            'nitrate'         => ['label' => 'Nitrate',        'csv' => 'Nitrate (NO3-N) mg/L'],
            'nitrite'         => ['label' => 'Nitrite',        'csv' => 'Nitrite (NO2-N) mg/L'],

            // CSV only
            'ph'              => ['csv' => 'pH',                      'export_only' => true],
            'bod'             => ['csv' => 'BOD (Check unit)',        'export_only' => true],
            'aluminium'       => ['csv' => 'Aluminium mg/L',          'export_only' => true],
            'barium'          => ['csv' => 'Barium (Ba) mg/L',        'export_only' => true],
            'iron'            => ['csv' => 'Iron (Fe) mg/L',          'export_only' => true],
            'chrome'          => ['csv' => 'Chrome (Cr) mg/L',        'export_only' => true],
            'cadmium'         => ['csv' => 'Cadmium (Cd) mg/L',       'export_only' => true],
            'manganese'       => ['csv' => 'Manganese (Mn) mg/L',     'export_only' => true],
            'nickel'          => ['csv' => 'Nickel (Ni) mg/L',        'export_only' => true],
            'zinc'            => ['csv' => 'Zinc (Zn) mg/L',          'export_only' => true],
            'copper'          => ['csv' => 'Copper (Cu) mg/L',        'export_only' => true],
            'lead'            => ['csv' => 'Lead (Pb) mg/L',          'export_only' => true],
            'cod'             => ['csv' => 'COD mg/L',                'export_only' => true],
            'tds'             => ['csv' => 'TDS mg/L',                'export_only' => true],
            'tss'             => ['csv' => 'TSS mg/L',                'export_only' => true],
            'phosphate'       => ['csv' => 'Phosphate mg/L',          'export_only' => true],
            'oilgrease'       => ['csv' => 'Oil and grease mg/L',     'export_only' => true],
            'sulfide'         => ['csv' => 'Sulfide mg/L',            'export_only' => true],
            'tot_nitrogen'    => ['csv' => 'Total Nitrogen mg/L',     'export_only' => true],
            'tot_phosphorous' => ['csv' => 'Total Phosphorous mg/L',  'export_only' => true],

            'notes'           => ['label' => 'Notes', 'csv' => 'Notes', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['barcode_sample', 'asc']],

        // second button: converted values (same formulas as the old model)
        'exports' => [
            'conversion' => [
                'button'   => 'Export Conversion',
                'filename' => 'Water_Chemistry_Conversion',
                'joins' => [
                    [$waterSubChem, 'c.barcode', 't.barcode_sample'],
                    ['ref_sampletype as b', 'b.id_sampletype', 'c.id_type2bwat'],
                    ['obj2b_receipt as d',  'd.barcode_sample', 'c.barcode_sample', ['d.lab = t.lab']],
                ],
                'where' => ['c.lab IS NOT NULL'],
                'columns' => [
                    'barcode_sample'  => ['csv' => 'Barcode Sample'],
                    'date_process'    => ['csv' => 'Date process',   'sql' => 'd.date_arrival'],
                    'water_lab'       => ['csv' => 'Laboratory',     'sql' => 'c.lab'],
                    'parent_barcode'  => ['csv' => 'Parent barcode', 'sql' => 'c.barcode_sample'],
                    'sampletype2bwat' => ['csv' => 'Water type',     'sql' => 'b.sampletype'],
                    'ammonia' => [
                        'csv' => 'Ammonia conversion (NH3-N) mg/L',
                        'sql' => 'CASE WHEN c.id_type2bwat NOT IN (11, 15, 16, 17) THEN t.ammonia * 0.8224 ELSE t.ammonia END',
                    ],
                    'nitrate' => [
                        'csv' => 'Nitrate conversion (NO3-N) mg/L',
                        'sql' => 'CASE WHEN c.id_type2bwat IN (11, 15, 16, 17) THEN (CASE WHEN t.nitrate * 0.2259 = 0 THEN t.nitrate ELSE t.nitrate * 0.2259 END) WHEN {lab} = 1 THEN t.nitrate END',
                    ],
                    'nitrite' => [
                        'csv' => 'Nitrite conversion (NO2-N) mg/L',
                        'sql' => 'CASE WHEN c.id_type2bwat IN (11, 15, 16, 17) THEN (CASE WHEN t.nitrite * 0.3045 = 0 THEN t.nitrite ELSE t.nitrite * 0.3045 END) WHEN {lab} = 1 THEN t.nitrite END',
                    ],
                    'notes' => ['csv' => 'Notes'],
                ],
                'order' => [['date_process', 'desc'], ['barcode_sample', 'asc']],
            ],
        ],
    ],

    'wat_water_microbiology' => [
        'title' => 'Water Microbiology',
        'table' => 'obj2b_wat_microby',
        'joins' => [
            [$waterSubMicro, 'c.barcode', 't.barcode_sample'],
            ['ref_sampletype as b', 'b.id_sampletype', 'c.id_type2bwat'],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode Sample',  'csv' => 'Barcode_sample', 'class' => 'fw-semibold'],
            'date_process'    => ['label' => 'Date Process',    'csv' => 'Date_process'],
            'water_lab'       => ['label' => 'Laboratory',      'csv' => 'Water_lab',      'sql' => 'c.lab'],
            'parent_barcode'  => ['label' => 'Parent Barcode',  'csv' => 'Parent_barcode', 'sql' => 'c.barcode_sample'],
            'sampletype'      => ['label' => 'Water Type',      'csv' => 'Sample_type',    'sql' => 'b.sampletype'],
            'total_coliforms' => ['label' => 'Total Coliforms', 'csv' => 'Total_coliforms'],
            'volume_ecoli'    => ['label' => 'Volume E. coli',  'csv' => 'Volume_ecoli'],
            'comments'        => ['label' => 'Comments',        'csv' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_process', 'desc'], ['barcode_sample', 'asc']],
    ],

    'wat_water_spectroqc' => [
        'title' => 'Water Spectro QC',
        'table' => 'obj2b_spectro_crm',
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'id_spec'        => ['label' => 'ID',                 'class' => 'fw-semibold'],
            'date_spec'      => ['label' => 'Date Spectro'],
            'initial'        => ['label' => 'Lab Tech',           'sql' => 'p.initial'],
            'chem_parameter' => ['label' => 'Chemistry Parameter'],
            'lot_no'         => ['label' => 'Lot Number'],
            'date_expired'   => ['label' => 'Date Expired'],
            'cert_value'     => ['label' => 'Cert. Value'],
            'notes'          => ['label' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_spec', 'desc'], ['id_spec', 'asc']],

        // Excel export with two sheets, same layout and headers as the old file
        'export_format' => 'xlsx',
        'sheets' => [
            [
                'name'  => 'Water_Spectro',
                'table' => 'obj2b_spectro_crm',
                'joins' => [['ref_person as c', 'c.id_person', 't.id_person']],
                'columns' => [
                    'id_spec'        => ['csv' => 'ID_spectro'],
                    'date_spec'      => ['csv' => 'Date_spectro'],
                    'initial'        => ['csv' => 'Lab_tech', 'sql' => 'c.initial'],
                    'chem_parameter' => ['csv' => 'Chemistry_parameter'],
                    'mixture_name'   => ['csv' => 'Mixture_name'],
                    'sample_no'      => ['csv' => 'Sample_number'],
                    'lot_no'         => ['csv' => 'Lot_number'],
                    'date_expired'   => ['csv' => 'Date_expired'],
                    'cert_value'     => ['csv' => 'Certified_value'],
                    'uncertainty'    => ['csv' => 'Uncertainty'],
                    'notes'          => ['csv' => 'Comments', 'sql' => 'TRIM(t.notes)'],
                    'tot_result'     => ['csv' => 'Total_result'],
                    'tot_trueness'   => ['csv' => 'Total_trueness'],
                    'tot_bias'       => ['csv' => 'Total_bias'],
                    'avg_result'     => ['csv' => 'AVG_result'],
                    'avg_trueness'   => ['csv' => 'AVG_trueness'],
                    'avg_bias'       => ['csv' => 'AVG_bias'],
                    'sd'             => ['csv' => 'SD'],
                    'rsd'            => ['csv' => '%RSD'],
                    'cv_horwits'     => ['csv' => '%CV_horwits'],
                    'cv'             => ['csv' => '0.67x%CV'],
                    'prec'           => ['csv' => 'Test_Precision'],
                    'accuracy'       => ['csv' => 'Test_Accuracy'],
                    'bias'           => ['csv' => 'Test_Bias'],
                ],
                'order' => [['date_spec', 'desc'], ['id_spec', 'asc']],
            ],
            [
                'name'  => 'Water_spectro_QC_detail',
                'table' => 'obj2b_spectro_crm_det',
                'joins' => [],
                'columns' => [
                    'id_dspec'    => ['csv' => 'ID_detail_spectro'],
                    'id_spec'     => ['csv' => 'ID_parent_spectro'],
                    'duplication' => ['csv' => 'Duplication'],
                    'result'      => ['csv' => 'Result'],
                    'trueness'    => ['csv' => 'Trueness'],
                    'bias_method' => ['csv' => 'Bias_method'],
                    'result2'     => ['csv' => 'Result^2'],
                ],
                'order' => [['id_spec', 'asc'], ['id_dspec', 'asc']],
            ],
        ],
    ],

    // ============================================================ FREEZER MODULE

    'freezer_in' => [
        'title' => 'Freezer - Sample IN',
        'table' => 'freezer_in',
        'joins' => [
            ['ref_person as p',      'p.id_person',      't.id_person'],
            ['ref_vessel as v',      'v.id_vessel',      't.id_vessel'],
            ['ref_location_80 as l', 'l.id_location_80', 't.id_location_80', ['l.lab = t.lab']],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode Sample', 'class' => 'fw-semibold'],
            'date_in'        => ['label' => 'Date IN'],
            'time_in'        => ['sql' => "DATE_FORMAT(t.time_in, '%H:%i')", 'export_only' => true],
            'initial'        => ['label' => 'Initial', 'sql' => 'p.initial'],
            'vessel'         => ['label' => 'Vessel',  'sql' => 'v.vessel'],
            'cryobox'        => ['label' => 'Cryobox'],
            'location'       => ['label' => 'Location', 'sql' => "CONCAT('F', l.freezer, '-', 'S', l.shelf, '-', 'R', l.rack, '-', 'DRW', l.rack_level)"],
            'tube_position'  => ['label' => 'Tube Position'],
            'comments'       => ['label' => 'Comments', 'class' => 'col-trunc'],
        ],
        'order' => [['date_in', 'desc'], ['time_in', 'desc'], ['barcode_sample', 'asc']],

        // the CSV has its own column order (as in the old file)
        'export' => [
            'columns' => [
                'id'             => ['csv' => 'ID'],
                'date_in'        => ['csv' => 'Date_in'],
                'time_in'        => ['csv' => 'Time_in', 'sql' => "DATE_FORMAT(t.time_in, '%H:%i')"],
                'initial'        => ['csv' => 'Lab_tech', 'sql' => 'p.initial'],
                'vessel'         => ['csv' => 'Vessel',   'sql' => 'v.vessel'],
                'barcode_sample' => ['csv' => 'Barcode_vessel'],
                'need_cryobox'   => ['csv' => 'Associated_cryobox'],
                'cryobox'        => ['csv' => 'Barcode_cryobox'],
                'location'       => ['csv' => 'Location', 'sql' => "CONCAT('F', l.freezer, '-', 'S', l.shelf, '-', 'R', l.rack, '-', 'DRW', l.rack_level)"],
                'tube_position'  => ['csv' => 'Tube_position'],
                'comments'       => ['csv' => 'Comments'],
            ],
            'order' => [['date_in', 'desc'], ['time_in', 'desc'], ['barcode_sample', 'asc']],
        ],
    ],

    'freezer_out' => [
        'title' => 'Freezer - Sample OUT',
        'table' => 'freezer_out',
        'joins' => [
            ['ref_person as p',      'p.id_person',      't.id_person'],
            ['ref_sample as s',      's.id_sample',      't.id_sample'],
            ['ref_vessel as v',      'v.id_vessel',      't.id_vessel'],
            ['ref_destination as e', 'e.id_destination', 't.id_destination'],
            ['ref_shipping as f',    'f.id_shipping',    't.id_shipping'],
        ],
        'columns' => $freezerOutColumns,
        'order'   => [['date_out', 'desc'], ['id', 'asc']],

        // second button: samples that are used up (destination other than 4), the newest OUT record of each barcode
        'exports' => [
            'usedup' => [
                'button'   => 'Export Used UP',
                'filename' => 'Freezer_Sample_UsedUP',
                // all joins are listed again because a variant replaces the module's joins
                'joins' => [
                    ['ref_person as p',      'p.id_person',      't.id_person'],
                    ['ref_sample as s',      's.id_sample',      't.id_sample'],
                    ['ref_vessel as v',      'v.id_vessel',      't.id_vessel'],
                    ['ref_destination as e', 'e.id_destination', 't.id_destination'],
                    ['ref_shipping as f',    'f.id_shipping',    't.id_shipping'],
                    // same as the old LIMS: the row must be one of the lab's OUT records with destination <> 4 (matched by uuid)
                    ['(SELECT fo.uuid
                         FROM freezer_out fo
                         JOIN ref_destination d ON d.id_destination = fo.id_destination
                        WHERE fo.lab = {lab} AND fo.flag = 0 AND d.id_destination <> 4
                        GROUP BY fo.barcode_sample, fo.uuid) as x', 'x.uuid', 't.uuid'],
                ],
                'where'    => ['e.id_destination <> 4', 'x.uuid IS NOT NULL'],
                'columns'  => $freezerOutColumns,
                'order'    => [['date_out', 'desc'], ['id', 'asc']],
            ],
        ],
    ],

/*
 * Sample External (5 modul), tempel di dalam array return config/modules.php
 * (di bawah modul-modul yang sudah ada). Tidak perlu mengubah ModuleController,
 * web.php, maupun index.blade.php.
 *
 * Catatan:
 * - Tidak ada join: semua nilai ditampilkan apa adanya.
 * - Kolom 'export_only' => true hanya muncul di CSV (urutan CSV = urutan di bawah).
 * - 'csv' => header CSV, disamakan dengan header CSV di LIMS lama.
 * - Urutan default: kolom tanggal DESC, lainnya ASC.
 * - Filter lab = lab terpilih dan flag = 0 otomatis oleh applyScope().
 */

    // ------------------------------------------------------------ Sample Reception
    'se_sample_reception' => [
        'title' => 'Sample Reception',
        'table' => 'se_sample_receipt',
        'order' => [
            ['date_received', 'desc'],
            ['barcode_sample', 'asc'],
            ['obtained', 'asc'],
        ],
        'columns' => [
            'barcode_sample'       => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'new_barcode'          => ['label' => 'New barcode', 'csv' => 'New_barcode'],
            'date_received'        => ['label' => 'Date received', 'csv' => 'Date_received'],
            'lab_received'         => ['label' => 'Lab received', 'csv' => 'Lab_received'],
            'person'               => ['label' => 'Person', 'csv' => 'Person', 'export_only' => true],
            'sample_type'          => ['label' => 'Sample type', 'csv' => 'Sample_type'],
            'obtained'             => ['label' => 'Obtained from', 'csv' => 'Obtained'],
            'conditions'           => ['label' => 'Condition', 'csv' => 'Conditions'],
            'quarantine'           => ['label' => 'Quarantine', 'csv' => 'Quarantine', 'export_only' => true],
            'permit_number'        => ['label' => 'Permit number', 'csv' => 'Permit_number', 'export_only' => true],
            'name_email_custodian' => ['label' => 'Name email custodian', 'csv' => 'Name_email_custodian', 'export_only' => true],
            'desc_storage'         => ['label' => 'Desc storage', 'csv' => 'Desc_storage', 'export_only' => true],
            'loc_storage'          => ['label' => 'Loc storage', 'csv' => 'Loc_storage', 'export_only' => true],
            'comments'             => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // ------------------------------------------------------- Sample Analysis Summary
    'se_sample_analysis' => [
        'title' => 'Sample Analysis Summary',
        'table' => 'se_analysis',
        'order' => [
            ['date_analysis', 'desc'],
            ['barcode_sample', 'asc'],
            ['analysis', 'asc'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'date_analysis'  => ['label' => 'Date analysis', 'csv' => 'Date_analysis'],
            'analysis'       => ['label' => 'Analysis', 'csv' => 'Description_analysis'],
            'person'         => ['label' => 'Person', 'csv' => 'Person_analysis'],
            'comments'       => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // ------------------------------------------------------------- Sample Result
    'se_sample_result' => [
        'title' => 'Sample Result',
        'table' => 'se_result',
        'order' => [
            ['date_analysis', 'desc'],
            ['barcode_sample', 'asc'],
            ['analyte', 'asc'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'date_analysis'  => ['label' => 'Date analysis', 'csv' => 'Date_analysis'],
            'analyte'        => ['label' => 'Analyte', 'csv' => 'Analyte'],
            'result'         => ['label' => 'Result', 'csv' => 'Result'],
            'units'          => ['label' => 'Unit', 'csv' => 'Unit'],
            'person'         => ['label' => 'Person', 'csv' => 'Person'],
            'comments'       => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // -------------------------------------------------------------- Sample Ended
    'se_sample_ended' => [
        'title' => 'Sample Ended',
        'table' => 'se_sample_ended',
        'order' => [
            ['date_ended', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'date_ended'     => ['label' => 'Date ended', 'csv' => 'Date_ended'],
            'lab_sample_end' => ['label' => 'Lab sample end', 'csv' => 'Lab_sample_end'],
            'comments'       => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // ------------------------------------------------------ Sample Sent to Other Lab
    'se_sample_sent_other' => [
        'title' => 'Sample Sent to Other Lab',
        'table' => 'se_sample_sent',
        'order' => [
            ['date_shipped', 'desc'],
            ['barcode_sample', 'asc'],
            ['new_barcode', 'asc'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'new_barcode'    => ['label' => 'New barcode', 'csv' => 'New_barcode'],
            'sample_type'    => ['label' => 'Sample type', 'csv' => 'Sample_type'],
            'date_shipped'   => ['label' => 'Date shipped', 'csv' => 'Date_shipped'],
            'volume'         => ['label' => 'Volume', 'csv' => 'Volume'],
            'sent_from'      => ['label' => 'Sent from', 'csv' => 'Sent_from', 'export_only' => true],
            'destination'    => ['label' => 'Destination', 'csv' => 'Destination'],
            'custodian'      => ['label' => 'Custodian', 'csv' => 'Custodian'],
            'comments'       => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

/*
 * DNA (6 modul): tempel di dalam array return config/modules.php
 * (jangan salin baris <?php dan komentar ini).
 *
 * Aturan yang dipakai:
 * - lab = lab terpilih dan flag = 0 otomatis (applyScope); semua join ke tabel
 *   yang id-nya berulang antar lab difilter lab juga.
 * - Urutan default: kolom tanggal DESC, lainnya ASC.
 * - Kolom Lab tech = ref_person.initial.
 * - 'export_only' => hanya muncul di CSV. 'csv' => header CSV seperti LIMS lama.
 * - Pada 'export' (tunggal), setiap kolom harus punya 'sql' eksplisit.
 */

    // ------------------------------------------------------------ DNA Sample Control
    'dna_sample_control' => [
        'title' => 'DNA Sample Control',
        'table' => 'dna_control',
        'order' => [
            ['barcode_sample', 'asc'],
        ],
        'joins' => [
            ['ref_sampledna as s', 's.id_sample', 't.id_sample'],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'sample_type'     => ['label' => 'Sample type', 'csv' => 'Sample_type', 'sql' => 's.sample'],
            'barcode_vessel'  => ['label' => 'Barcode vessel', 'csv' => 'Barcode_vessel'],
            'barcode_vessel2' => ['label' => 'Barcode vessel2', 'csv' => 'Barcode_vessel2'],
            'barcode_vessel3' => ['label' => 'Barcode vessel3', 'csv' => 'Barcode_vessel3'],
            'barcode_vessel4' => ['label' => 'Barcode vessel4', 'csv' => 'Barcode_vessel4'],
            'barcode_vessel5' => ['label' => 'Barcode vessel5', 'csv' => 'Barcode_vessel5'],
            'comments'        => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'export_only' => true],
        ],
    ],

    // -------------------------------------------------------------- DNA Extraction
    // Tabel di layar tanpa join (cepat). Lokasi freezer dan parent sample hanya
    // dihitung saat export CSV, memakai query seperti get_all() LIMS lama.
    'dna_extraction' => [
        'title' => 'DNA Extraction',
        'table' => 'dna_extraction',
        'order' => [
            ['date_extraction', 'desc'],
            ['barcode_sample', 'asc'],
            ['barcode_dna', 'asc'],
        ],
        'columns' => [
            'barcode_sample'       => ['label' => 'Barcode sample'],
            'date_extraction'      => ['label' => 'Date extraction'],
            'sampletype'           => ['label' => 'Sample type'],
            'weights'              => ['label' => 'Weights'],
            'barcode_dna'          => ['label' => 'Barcode DNA'],
            'cryobox'              => ['label' => 'Cryobox'],
            'barcode_metagenomics' => ['label' => 'Barcode metagenomics'],
            'meta_box'             => ['label' => 'Meta box'],
            'comments'             => ['label' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
        'export' => [
            'filename' => 'dna_extraction',
            'order' => [
                ['date_extraction', 'desc'],
                ['source_barcode_sample', 'asc'],
                ['barcode_dna', 'asc'],
            ],
            'joins' => [
                ['ref_person as p', 'p.id_person', 't.id_person'],
                // parent sample: view v_dna_all_sample, hanya milik lab terpilih
                ['(SELECT * FROM v_dna_all_sample WHERE lab = {lab}) as c', 'c.barcode', 't.barcode_sample'],
                // record freezer_in per barcode DNA (sama seperti LIMS lama: MIN(date_in+time_in)), dibatasi lab
                ['(SELECT fi.barcode_sample, fi.id_location_80
                     FROM freezer_in fi
                     JOIN (SELECT barcode_sample, MIN(CONCAT(date_in, time_in)) AS m
                             FROM freezer_in WHERE lab = {lab} GROUP BY barcode_sample) fm
                       ON fi.barcode_sample = fm.barcode_sample AND CONCAT(fi.date_in, fi.time_in) = fm.m
                    WHERE fi.lab = {lab}) as g', 'g.barcode_sample', 't.barcode_dna'],
                ['ref_location_80 as d', 'd.id_location_80', 'g.id_location_80', ['d.lab = t.lab']],
            ],
            'columns' => [
                'barcode_dna'           => ['label' => 'Barcode DNA', 'csv' => 'Barcode_DNA', 'sql' => 't.barcode_dna'],
                'source_barcode_sample' => ['label' => 'Barcode sample', 'csv' => 'Source_Barcode_sample', 'sql' => 't.barcode_sample'],
                'date_extraction'       => ['label' => 'Date extraction', 'csv' => 'Date_extraction', 'sql' => 't.date_extraction'],
                'initial'               => ['label' => 'Lab tech', 'csv' => 'Lab_tech', 'sql' => 'p.initial'],
                'kit_lot'               => ['label' => 'Kit lot', 'csv' => 'Kit_lot', 'sql' => 't.kit_lot'],
                'sampletype'            => ['label' => 'Sample type', 'csv' => 'Sample_type', 'sql' => 't.sampletype'],
                'parent_barcode_sample' => ['label' => 'Parent barcode sample', 'csv' => 'Parent_Barcode_Sample', 'sql' => 'c.Barcode_sample'],
                'parent_sample_type'    => ['label' => 'Parent sample type', 'csv' => 'Parent_Sample_Type', 'sql' => 'c.sampletype'],
                'weights'               => ['label' => 'Weights', 'csv' => 'Weights', 'sql' => 't.weights'],
                'tube_number'           => ['label' => 'Tube number', 'csv' => 'Tube_number', 'sql' => 't.tube_number'],
                'cryobox'               => ['label' => 'Cryobox', 'csv' => 'Cryobox', 'sql' => 't.cryobox'],
                'barcode_metagenomics'  => ['label' => 'Barcode metagenomics', 'csv' => 'Barcode_metagenomics', 'sql' => 't.barcode_metagenomics'],
                'location'              => ['label' => 'Location', 'csv' => 'Location', 'sql' => "CONCAT('F', d.freezer, '-S', d.shelf, '-R', d.rack, '-DRW', d.rack_level)"],
                'meta_box'              => ['label' => 'Meta box', 'csv' => 'Meta_box', 'sql' => 't.meta_box'],
                'qc_status'             => ['label' => 'QC status', 'csv' => 'QC_Status', 'sql' => 't.qc_status'],
                'comments'              => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
            ],
        ],
    ],

    // ----------------------------------------------------------- DNA Concentration
    'dna_concentration' => [
        'title' => 'DNA Concentration',
        'table' => 'dna_concentration',
        'order' => [
            ['date_concentration', 'desc'],
            ['barcode_dna', 'asc'],
        ],
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
            // dna_extraction ikut difilter lab dan flag (barcode bisa berulang antar lab)
            ['dna_extraction as e', 'e.barcode_dna', 't.barcode_dna', ['e.lab = t.lab', 'e.flag = 0']],
        ],
        'columns' => [
            'barcode_dna'       => ['label' => 'Barcode DNA', 'csv' => 'Barcode_DNA'],
            'date_concentration'=> ['label' => 'Date concentration', 'csv' => 'Date_concentration'],
            'initial'           => ['label' => 'Lab tech', 'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'sampletype'        => ['label' => 'Sample type', 'csv' => 'Sample_type', 'sql' => 'e.sampletype'],
            'dna_concentration' => ['label' => 'DNA concentration', 'csv' => 'DNA_concentration'],
            'comments'          => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],


    // ------------------------------------------------------------- DNA Aliquotting
    // Child row (+ di kolom ID): Row, Column, Barcode DNA, Comments.
    // Export CSV: satu baris per sumur, seperti get_all() LIMS lama.
    'dna_aliquotting' => [
        'title' => 'DNA Aliquotting',
        'table' => 'dna_aliquot',
        'order' => [
            ['date_aliquot', 'desc'],
            ['id_dna', 'asc'],
        ],
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
            // jumlah aliquot per ID (dihitung dari detail, lab terpilih, flag = 0)
            ['(SELECT id_dna, COUNT(*) AS num_aliq FROM dna_aliquot_det
                WHERE lab = {lab} AND flag = 0 GROUP BY id_dna) as c', 'c.id_dna', 't.id_dna'],
        ],
        'columns' => [
            'id_dna'            => ['label' => 'ID'],
            'date_aliquot'      => ['label' => 'Date aliquot'],
            'initial'           => ['label' => 'Lab tech', 'sql' => 'p.initial'],
            'barcode_monash'    => ['label' => 'Barcode Monash'],
            'barcode_cambridge' => ['label' => 'Barcode Cambridge'],
            'num_aliq'          => ['label' => '#Aliquot', 'sql' => 'IFNULL(c.num_aliq, 0)'],
            'comments'          => ['label' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
        'detail' => [
            'id_column' => 'id_dna',
            'table'     => 'dna_aliquot_det',
            'key'       => 'd.id_dna',
            'parent'    => 't.id_dna',
            'order'     => 'd.row_id ASC, CAST(d.column_id AS UNSIGNED) ASC',
            'columns'   => [
                'row_id'      => ['label' => 'Row'],
                'column_id'   => ['label' => 'Column'],
                'barcode_dna' => ['label' => 'Barcode DNA'],
                'comments'    => ['label' => 'Comments', 'sql' => 'TRIM(d.comments)'],
            ],
        ],
        // CSV mengikuti get_all() LIMS lama: satu baris per sumur (detail) per aliquot
        'export' => [
            'order' => [
                ['date_aliquot', 'desc'],
                ['barcode_monash', 'asc'],
                ['row_id', 'asc'],
                ['column_id', 'asc'],
            ],
            'joins' => [
                ['ref_person as p', 'p.id_person', 't.id_person'],
                // detail ikut difilter lab dan flag = 0 (id berulang antar lab)
                ['dna_aliquot_det as c', 'c.id_dna', 't.id_dna', ['c.lab = t.lab', 'c.flag = 0']],
            ],
            'columns' => [
                'barcode_dna'       => ['label' => 'Barcode DNA', 'csv' => 'Barcode_DNA', 'sql' => 'c.barcode_dna'],
                'date_aliquot'      => ['label' => 'Date aliquot', 'csv' => 'Date_aliquot', 'sql' => 't.date_aliquot'],
                'initial'           => ['label' => 'Lab tech', 'csv' => 'Lab_tech', 'sql' => 'p.initial'],
                'barcode_monash'    => ['label' => 'Barcode Monash', 'csv' => 'Barcode_Monash', 'sql' => 't.barcode_monash'],
                'barcode_cambridge' => ['label' => 'Barcode Cambridge', 'csv' => 'Barcode_Cambridge', 'sql' => 't.barcode_cambridge'],
                'row_id'            => ['label' => 'Row', 'csv' => 'Row_ID', 'sql' => 'c.row_id'],
                'column_id'         => ['label' => 'Column', 'csv' => 'Column_ID', 'sql' => 'CAST(c.column_id AS UNSIGNED)'],
                'comments'          => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
            ],
        ],
    ],
 


    // ---------------------------------------------------------- DNA Sample Analysis
    'dna_sample_analysis' => [
        'title' => 'DNA Sample Analysis',
        'table' => 'dna_sample_analysis',
        'order' => [
            ['date_analysis', 'desc'],
            ['barcode_dna', 'asc'],
            ['analysis_type', 'asc'],
            ['run_number', 'asc'],
        ],
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'barcode_dna'   => ['label' => 'Barcode DNA', 'csv' => 'Barcode_DNA'],
            'date_analysis' => ['label' => 'Date analysis', 'csv' => 'Date_analysis'],
            'initial'       => ['label' => 'Lab tech', 'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'analysis_type' => ['label' => 'Analysis type', 'csv' => 'Analysis_type'],
            'run_number'    => ['label' => 'Run number', 'csv' => 'Run_number'],
            'barcode_array' => ['label' => 'Barcode array/Flowcell', 'csv' => 'Barcode_array/Flowcell'],
            'barcode_id'    => ['label' => 'Barcode ID', 'csv' => 'Barcode_id'],
            'comments'      => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // ---------------------------------------------------------- DNA Nanopore Result
    // Tabel ini tidak punya filter lab/flag di LIMS lama (data hasil upload CSV),
    // jadi datanya sama untuk kedua lab.
    'dna_nanopore_result' => [
        'title'       => 'DNA Nanopore Result',
        'table'       => 'dna_nanopore_result',
        'lab_column'  => null,
        'flag_column' => null,
        'order' => [
            ['Sample', 'asc'],
        ],
        'columns' => [
            'Sample'     => ['label' => 'Sample', 'csv' => 'Sample'],
            'Dups'       => ['label' => 'Dups', 'csv' => 'Dups'],
            'GC'         => ['label' => 'GC', 'csv' => 'GC'],
            'Median_len' => ['label' => 'Median_len', 'csv' => 'Median_len'],
            'Seqs'       => ['label' => 'Seqs', 'csv' => 'Seqs'],
            'Comment'    => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.Comment)', 'export_only' => true],
        ],
    ],    


    // ------------------------------------------------ Weights (B/T) - before collection
    'nhmrc_bootsocks_before' => [
        'title' => 'Weights (B/T) - before sample collection',
        'table' => 'nhmrc_bootsocks_before',
        'order' => [
            ['date_weighed', 'desc'],
            ['barcode_bootsocks', 'asc'],
        ],
        'columns' => [
            'barcode_bootsocks'   => ['label' => 'Barcode bootsocks/toys', 'csv' => 'Barcode_bootsocks'],
            'date_weighed'        => ['label' => 'Date weighed', 'csv' => 'Date_weighed'],
            'bootsock_weight_dry' => ['label' => 'Weighed - dry (g)', 'csv' => 'Weight_dry'],
            'comments'            => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],
 
    // ------------------------------------------------- Weights (B/T) - after collection
    'nhmrc_bootsocks_after' => [
        'title' => 'Weights (B/T) - after sample collection',
        'table' => 'nhmrc_bootsocks_after',
        'order' => [
            ['date_weighed', 'desc'],
            ['barcode_bootsocks', 'asc'],
        ],
        'columns' => [
            'barcode_bootsocks'   => ['label' => 'Barcode bootsocks/toys', 'csv' => 'Barcode_bootsocks'],
            'date_weighed'        => ['label' => 'Date weighed', 'csv' => 'Date_weighed'],
            'bootsock_weight_wet' => ['label' => 'Weighed - wet (g)', 'csv' => 'Weight_wet'],
            'comments'            => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],
 
    // ------------------------------------------------------------- Sample Reception
    'nhmrc_sample_reception' => [
        'title' => 'Sample Reception',
        'table' => 'nhmrc_receipt',
        'order' => [
            ['date_arrival', 'desc'],
            ['time_arrival', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'joins' => [
            ['ref_sampletype as b', 'b.id_sampletype', 't.id_type2b'],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'date_arrival'    => ['label' => 'Date arrive', 'csv' => 'Date_arrival'],
            'time_arrival'    => ['label' => 'Time arrive', 'csv' => 'Time_arrival', 'sql' => "DATE_FORMAT(t.time_arrival, '%H:%i')"],
            'sampletype2b'    => ['label' => 'Sample type', 'csv' => 'Sample_type', 'sql' => 'b.sampletype'],
            'png_control'     => ['label' => 'P&G control', 'csv' => 'P&G_control'],
            'barcode_tinytag' => ['label' => 'Barcode tinytag', 'csv' => 'Barcode_tinytag'],
            'comments'        => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],
 
    // ------------------------------------------------------ Stomacher (BOOTSOCKS/TOY)
    // Child row (IDEXX) terhubung lewat Barcode Bootsocks: nhmrc_subbs_idexx.barcode_sample.
    // CSV = format lebar (33 kolom) seperti get_all() LIMS lama, satu baris per sample reception.
    'nhmrc_bootsocks_stomacher' => [
        'title' => 'Stomacher (BOOTSOCKS/TOY)',
        'table' => 'nhmrc_bs_stomacher',
        'order' => [
            ['date_conduct', 'desc'],
            ['barcode_sample', 'asc'],
            ['elution_no', 'asc'],
        ],
        'columns' => [
            'barcode_sample'   => ['label' => 'Barcode Sample'],
            'date_conduct'     => ['label' => 'Date Conducted'],
            'elution_no'       => ['label' => 'Elution Number'],
            'barcode_bootsock' => ['label' => 'Barcode Bootsocks/Toys/Swab'],
            'elution'          => ['label' => 'Elution'],
            'barcode_falcon'   => ['label' => 'Barcode Falcon Tube'],
            'volume_stomacher' => ['label' => 'Volume recovered after stomacher (mL)'],
        ],
        'detail' => [
            'id_column' => 'barcode_bootsock',
            'title'     => 'IDEXX',
            'table'     => 'nhmrc_subbs_idexx',
            'key'       => 'd.barcode_sample',
            'parent'    => 't.barcode_bootsock',
            'order'     => 'd.barcode_sample ASC',
            'columns'   => [
                'barcode_sample'   => ['label' => 'Barcode bootsocks'],
                'barcode_colilert' => ['label' => 'Barcode IDEXX'],
                'barcode_falcon1'  => ['label' => 'Barcode falcon 1'],
                'volume_falcon1'   => ['label' => 'Volume falcon 1'],
                'barcode_falcon2'  => ['label' => 'Barcode falcon 2'],
                'volume_falcon2'   => ['label' => 'Volume falcon 2'],
                'dilution'         => ['label' => 'Dilution'],
                'time_incubation'  => ['label' => 'Time incubation start'],
                'comments'         => ['label' => 'Comments', 'sql' => 'TRIM(d.comments)'],
            ],
        ],
        'export' => [
            'table' => 'nhmrc_receipt',
            'where' => ['t.id_type2b IN (18,19,20,21,25,26)'],
            'order' => [
                ['stomacher_date_conduct', 'desc'],
                ['barcode_sample', 'asc'],
            ],
            'joins' => [
                ['nhmrc_bs_stomacher as g', 'g.barcode_sample', 't.barcode_sample', ["g.elution_no = 'Micro1'", 'g.lab = t.lab', 'g.flag = 0']],
                ['nhmrc_bs_stomacher as o', 'o.barcode_sample', 't.barcode_sample', ["o.elution_no = 'Micro2'", 'o.lab = t.lab', 'o.flag = 0']],
                ['nhmrc_bs_stomacher as s', 's.barcode_sample', 't.barcode_sample', ["s.elution_no = 'Moisture1'", 's.lab = t.lab', 's.flag = 0']],
                ['nhmrc_bs_stomacher as m', 'm.barcode_sample', 't.barcode_sample', ["m.elution_no = 'Moisture2'", 'm.lab = t.lab', 'm.flag = 0']],
                ['nhmrc_subbs_idexx as j', 'j.barcode_sample', 'g.barcode_bootsock', ['j.lab = t.lab', 'j.flag = 0']],
                ['ref_sampletype as b', 'b.id_sampletype', 't.id_type2b'],
            ],
            'columns' => [
                'barcode_sample'                    => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample', 'sql' => 't.barcode_sample'],
                'sampletype'                        => ['label' => 'Sample type', 'csv' => 'Sample_type', 'sql' => 'b.sampletype'],
                'stomacher_date_conduct'            => ['label' => 'Date conduct', 'csv' => 'Date_conduct', 'sql' => 'g.date_conduct'],
                'stomacher_barcode_bootsocks1'      => ['label' => 'Barcode bootsocks1', 'csv' => 'Barcode_bootsocks1', 'sql' => 'g.barcode_bootsock'],
                'stomacher_elution_number_Micro1'   => ['label' => 'Elution number Micro1', 'csv' => 'Elution_number_Micro1', 'sql' => 'g.elution_no'],
                'stomacher_elution_Micro1'          => ['label' => 'Elution Micro1', 'csv' => 'Elution_Micro1', 'sql' => 'g.elution'],
                'stomacher_elution_Micro1_comment'  => ['label' => 'Elution Micro1 comment', 'csv' => 'Elution_Micro1_comment', 'sql' => 'TRIM(g.elu_comments)'],
                'stomacher_barcode_falcon_Micro1'   => ['label' => 'Barcode falcon Micro1', 'csv' => 'Barcode_falcon_Micro1', 'sql' => 'g.barcode_falcon'],
                'stomacher_volume_Micro1'           => ['label' => 'Volume Micro1', 'csv' => 'Volume_Micro1', 'sql' => 'g.volume_stomacher'],
                'stomacher_elution_number_Micro2'   => ['label' => 'Elution number Micro2', 'csv' => 'Elution_number_Micro2', 'sql' => 'o.elution_no'],
                'stomacher_elution_Micro2'          => ['label' => 'Elution Micro2', 'csv' => 'Elution_Micro2', 'sql' => 'o.elution'],
                'stomacher_elution_Micro2_comment'  => ['label' => 'Elution Micro2 comment', 'csv' => 'Elution_Micro2_comment', 'sql' => 'TRIM(o.elu_comments)'],
                'stomacher_barcode_falcon_Micro2'   => ['label' => 'Barcode falcon Micro2', 'csv' => 'Barcode_falcon_Micro2', 'sql' => 'o.barcode_falcon'],
                'stomacher_volume_Micro2'           => ['label' => 'Volume Micro2', 'csv' => 'Volume_Micro2', 'sql' => 'o.volume_stomacher'],
                'stomacher_barcode_bootsocks2'      => ['label' => 'Barcode bootsocks2', 'csv' => 'Barcode_bootsocks2', 'sql' => 's.barcode_bootsock'],
                'stomacher_elution_number_Moisture1'=> ['label' => 'Elution number Moisture1', 'csv' => 'Elution_number_Moisture1', 'sql' => 's.elution_no'],
                'stomacher_elution_Moisture1'       => ['label' => 'Elution Moisture1', 'csv' => 'Elution_Moisture1', 'sql' => 's.elution'],
                'stomacher_elution_Moisture1_comment'=> ['label' => 'Elution Moisture1 comment', 'csv' => 'Elution_Moisture1_comment', 'sql' => 'TRIM(s.elu_comments)'],
                'stomacher_barcode_falcon_Moisture1'=> ['label' => 'Barcode falcon Moisture1', 'csv' => 'Barcode_falcon_Moisture1', 'sql' => 's.barcode_falcon'],
                'stomacher_volume_Moisture1'        => ['label' => 'Volume Moisture1', 'csv' => 'Volume_Moisture1', 'sql' => 's.volume_stomacher'],
                'stomacher_elution_number_Moisture2'=> ['label' => 'Elution number Moisture2', 'csv' => 'Elution_number_Moisture2', 'sql' => 'm.elution_no'],
                'stomacher_elution_Moisture2'       => ['label' => 'Elution Moisture2', 'csv' => 'Elution_Moisture2', 'sql' => 'm.elution'],
                'stomacher_elution_Moisture2_comment'=> ['label' => 'Elution Moisture2 comment', 'csv' => 'Elution_Moisture2_comment', 'sql' => 'TRIM(m.elu_comments)'],
                'stomacher_barcode_falcon_Moisture2'=> ['label' => 'Barcode falcon Moisture2', 'csv' => 'Barcode_falcon_Moisture2', 'sql' => 'm.barcode_falcon'],
                'stomacher_volume_Moisture2'        => ['label' => 'Volume Moisture2', 'csv' => 'Volume_Moisture2', 'sql' => 'm.volume_stomacher'],
                'stom_idexx_barcode_colilert'       => ['label' => 'Idexx barcode colilert', 'csv' => 'Idexx_barcode_colilert', 'sql' => 'j.barcode_colilert'],
                'stom_idexx_barcode_falcon1'        => ['label' => 'Idexx barcode falcon1', 'csv' => 'Idexx_barcode_falcon1', 'sql' => 'j.barcode_falcon1'],
                'stom_idexx_volume_falcon1'         => ['label' => 'Idexx volume falcon1', 'csv' => 'Idexx_volume_falcon1', 'sql' => 'j.volume_falcon1'],
                'stom_idexx_barcode_falcon2'        => ['label' => 'Idexx barcode falcon2', 'csv' => 'Idexx_barcode_falcon2', 'sql' => 'j.barcode_falcon2'],
                'stom_idexx_volume_falcon2'         => ['label' => 'Idexx volume falcon2', 'csv' => 'Idexx_volume_falcon2', 'sql' => 'j.volume_falcon2'],
                'stom_idexx_dilution'               => ['label' => 'Idexx dilution', 'csv' => 'Idexx_dilution', 'sql' => 'j.dilution'],
                'stom_idexx_time_incu_start'        => ['label' => 'Idexx time incu start', 'csv' => 'Idexx_time_incu_start', 'sql' => 'j.time_incubation'],
                'stom_idexx_comments'               => ['label' => 'Idexx comments', 'csv' => 'Idexx_comments', 'sql' => 'TRIM(j.comments)'],
            ],
        ],
    ],
 
    // ------------------------------------------------------------- Stomacher (FOOD)
    // Child row (IDEXX) terhubung lewat Barcode food: nhmrc_subsd_idexx.barcode_food.
    'nhmrc_sample_prep' => [
        'title' => 'Stomacher (FOOD)',
        'table' => 'nhmrc_sample_prep',
        'order' => [
            ['date_conduct', 'desc'],
            ['barcode_sample', 'asc'],
            ['elution_no', 'asc'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode sample'],
            'date_conduct'   => ['label' => 'Date conducted'],
            'elution_no'     => ['label' => 'Elution number'],
            'barcode_food'   => ['label' => 'Barcode food'],
            'elution'        => ['label' => 'Elution'],
            'food_weight'    => ['label' => 'Food weight (g)'],
        ],
        'detail' => [
            'id_column' => 'barcode_food',
            'title'     => 'IDEXX',
            'table'     => 'nhmrc_subsd_idexx',
            'key'       => 'd.barcode_food',
            'parent'    => 't.barcode_food',
            'order'     => 'd.barcode_food ASC',
            'columns'   => [
                'barcode_food'     => ['label' => 'Barcode food'],
                'barcode_colilert' => ['label' => 'Barcode IDEXX'],
                'barcode_falcon1'  => ['label' => 'Barcode falcon 1'],
                'volume_falcon1'   => ['label' => 'Volume falcon 1'],
                'barcode_falcon2'  => ['label' => 'Barcode falcon 2'],
                'volume_falcon2'   => ['label' => 'Volume falcon 2'],
                'dilution'         => ['label' => 'Dilution'],
                'time_incubation'  => ['label' => 'Time incubation start'],
                'comments'         => ['label' => 'Comments', 'sql' => 'TRIM(d.comments)'],
            ],
        ],
        'export' => [
            'where' => ['b.id_type2b = 23'],
            'order' => [
                ['date_conduct', 'desc'],
                ['barcode_sample', 'asc'],
                ['elution_no', 'asc'],
            ],
            'joins' => [
                ['nhmrc_receipt as b', 'b.barcode_sample', 't.barcode_sample', ['b.lab = t.lab', 'b.flag = 0']],
                ['nhmrc_subsd_idexx as c', 'c.barcode_food', 't.barcode_food', ['c.lab = t.lab', 'c.flag = 0']],
                ['ref_sampletype as d', 'd.id_sampletype', 'b.id_type2b'],
            ],
            'columns' => [
                'barcode_sample'   => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample', 'sql' => 'b.barcode_sample'],
                'sampletype'       => ['label' => 'Sample type', 'csv' => 'Sample_type', 'sql' => 'd.sampletype'],
                'date_conduct'     => ['label' => 'Date conduct', 'csv' => 'Date_conduct', 'sql' => 't.date_conduct'],
                'elution_no'       => ['label' => 'Elution no', 'csv' => 'Elution_no', 'sql' => 't.elution_no'],
                'barcode_food'     => ['label' => 'Barcode food', 'csv' => 'Barcode_food', 'sql' => 't.barcode_food'],
                'elution'          => ['label' => 'Elution', 'csv' => 'Elution', 'sql' => 't.elution'],
                'elu_comments'     => ['label' => 'Elution comments', 'csv' => 'Elution_comments', 'sql' => 't.elu_comments'],
                'food_weight'      => ['label' => 'Food weight', 'csv' => 'Food_weight', 'sql' => 't.food_weight'],
                'barcode_colilert' => ['label' => 'Barcode colilert', 'csv' => 'Barcode_colilert', 'sql' => 'c.barcode_colilert'],
                'barcode_falcon1'  => ['label' => 'Barcode falcon1', 'csv' => 'Barcode_valcon1', 'sql' => 'c.barcode_falcon1'],
                'volume_falcon1'   => ['label' => 'Volume falcon1', 'csv' => 'Volume_valcon1', 'sql' => 'c.volume_falcon1'],
                'barcode_falcon2'  => ['label' => 'Barcode falcon2', 'csv' => 'Barcode_valcon2', 'sql' => 'c.barcode_falcon2'],
                'volume_falcon2'   => ['label' => 'Volume falcon2', 'csv' => 'Volume_valcon2', 'sql' => 'c.volume_falcon2'],
                'dilution'         => ['label' => 'Dilution', 'csv' => 'Dilution', 'sql' => 'c.dilution'],
                'time_incubation'  => ['label' => 'Time incubation', 'csv' => 'Time_incubation', 'sql' => 'c.time_incubation'],
                'comments'         => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(c.comments)'],
            ],
        ],
    ],
 
    // ----------------------------------------------------------------- Blank - In (IDEXX)
    'nhmrc_blank_in' => [
        'title' => 'Blank - In (IDEXX)',
        'table' => 'nhmrc_blanks',
        'order' => [
            ['date_conduct', 'desc'],
            ['time_incubation', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'date_conduct'    => ['label' => 'Date conducted', 'csv' => 'Date_conduct'],
            'time_incubation' => ['label' => 'Time incubation start', 'csv' => 'Time_incubation'],
            'blank_type'      => ['label' => 'Blank type', 'csv' => 'Blank_type'],
            'barcode_endidx'  => ['label' => 'Barcode IDEXX', 'csv' => 'barcode_end-idx'],
            'volume'          => ['label' => 'Volume (mL) added', 'csv' => 'Volume'],
            'dilution'        => ['label' => 'Dilution', 'csv' => 'Dilution'],
            'comments'        => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

/*
 * NHMRC batch 2 (6 modul): tempel di dalam array return config/modules.php
 * (jangan salin baris <?php dan komentar ini).
 *
 * Aturan: lab = lab terpilih dan flag = 0 otomatis. Lokasi freezer dihitung seperti
 * LIMS lama: modul.id_freezer = freezer_in.id (lab sama), lalu
 * freezer_in.id_location_80 = ref_location_80.id_location_80 (lab sama).
 * Kolom yang hanya ada di CSV (lokasi dll.) memakai 'export' tersendiri supaya
 * tabel di layar tidak ikut join dan tetap cepat.
 * Pada 'export' (tunggal), setiap kolom harus punya 'sql' eksplisit.
 */

    // ---------------------------------------------------------- IDEXX - In (Hand Rinse)
    'nhmrc_idexx_in' => [
        'title' => 'IDEXX - In (Hand Rinse)',
        'table' => 'nhmrc_idexx1',
        'order' => [
            ['date_conduct', 'desc'],
            ['time_incubation', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'columns' => [
            'barcode_sample'   => ['label' => 'Barcode sample'],
            'date_conduct'     => ['label' => 'Date conducted'],
            'time_incubation'  => ['label' => 'Time incubation started', 'export_only' => true],
            'barcode_colilert' => ['label' => 'Barcode Colilert'],
            'volume'           => ['label' => 'Volume (mL)'],
            'barcode_colilert2'=> ['label' => 'Barcode Colilert_2'],
            'volume2'          => ['label' => 'Volume_2 (mL)'],
            'comments'         => ['label' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
        'export' => [
            'order' => [
                ['date_conduct', 'desc'],
                ['time_incubation', 'desc'],
                ['barcode_sample', 'asc'],
            ],
            'columns' => [
                'barcode_sample'    => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample', 'sql' => 't.barcode_sample'],
                'date_conduct'      => ['label' => 'Date conducted', 'csv' => 'Date_conduct', 'sql' => 't.date_conduct'],
                'time_incubation'   => ['label' => 'Time incubation started', 'csv' => 'Time_incubation_started', 'sql' => 't.time_incubation'],
                'barcode_colilert'  => ['label' => 'Barcode Colilert', 'csv' => 'Barcode_colilert', 'sql' => 't.barcode_colilert'],
                'volume'            => ['label' => 'Volume (mL)', 'csv' => 'Volume(mL)added_in_bottle', 'sql' => 't.volume'],
                'dilution'          => ['label' => 'Dilution', 'csv' => 'Dilution', 'sql' => 't.dilution'],
                'comments'          => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
                'barcode_colilert2' => ['label' => 'Barcode Colilert_2', 'csv' => 'Barcode_colilert_2', 'sql' => 't.barcode_colilert2'],
                'volume2'           => ['label' => 'Volume_2 (mL)', 'csv' => 'Volume_2(mL)added_in_bottle', 'sql' => 't.volume2'],
                'dilution2'         => ['label' => 'Dilution_2', 'csv' => 'Dilution_2', 'sql' => 't.dilution2'],
                'comments2'         => ['label' => 'Comments_2', 'csv' => 'Comments_2', 'sql' => 'TRIM(t.comments2)'],
            ],
        ],
    ],

    // --------------------------------------------------------------- IDEXX - Out (All)
    'nhmrc_idexx_out' => [
        'title' => 'IDEXX - Out (All)',
        'table' => 'nhmrc_idexx2',
        'order' => [
            ['date_conduct', 'desc'],
            ['timeout_incubation', 'desc'],
            ['barcode_colilert', 'asc'],
        ],
        'columns' => [
            'barcode_colilert'     => ['label' => 'Barcode colilert', 'csv' => 'Barcode_colilert'],
            'date_conduct'         => ['label' => 'Date conducted', 'csv' => 'Date_conduct'],
            'timeout_incubation'   => ['label' => 'Time incubation finished', 'csv' => 'Time_incubation_finished',
                                       'sql' => "DATE_FORMAT(t.timeout_incubation, '%H:%i')", 'export_only' => true],
            'time_minutes'         => ['label' => 'Incubation duration (minutes)', 'csv' => 'Time_incubation_duration'],
            'ecoli_largewells'     => ['label' => 'Ecoli large wells', 'csv' => 'E.coli_large_wells'],
            'ecoli_smallwells'     => ['label' => 'Ecoli small wells', 'csv' => 'E.coli_small_wells'],
            'ecoli_mpn'            => ['label' => 'Ecoli MPN/100 mL', 'csv' => 'E.coli_MPN/100mL'],
            'coliforms_largewells' => ['label' => 'Coliform large wells', 'csv' => 'Coliforms_large_wells'],
            'coliforms_smallwells' => ['label' => 'Coliform small wells', 'csv' => 'Coliforms_small_wells'],
            'coliforms_mpn'        => ['label' => 'Coliform MPN/100 mL', 'csv' => 'Total_coliforms_MPN/100mL'],
            'comments'             => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'export_only' => true],
        ],
    ],

    // ------------------------------------------------------------------- Macconkey In
    'nhmrc_macconkey_in' => [
        'title' => 'Macconkey In',
        'table' => 'nhmrc_mac1',
        'order' => [
            ['date_process', 'desc'],
            ['time_process', 'desc'],
            ['bar_macconkey', 'asc'],
        ],
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'bar_macconkey'   => ['label' => 'Barcode macconkey', 'csv' => 'Barcode_macconkey'],
            'barcode_sample'  => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample/falcon1'],
            'barcode_falcon2' => ['label' => 'Barcode falcon 2', 'csv' => 'Barcode_falcon2', 'export_only' => true],
            'date_process'    => ['label' => 'Date process', 'csv' => 'Date_process'],
            'time_process'    => ['label' => 'Time process', 'csv' => 'Time_process'],
            'initial'         => ['label' => 'Person', 'csv' => 'Lab_tech', 'sql' => 'p.initial'],
            'volume'          => ['label' => 'Volume (mL)', 'csv' => 'Volume(mL)'],
            'comments'        => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // ------------------------------------------------------------------ Macconkey Out
    'nhmrc_macconkey_out' => [
        'title' => 'Macconkey Out',
        'table' => 'nhmrc_mac2',
        'order' => [
            ['date_process', 'desc'],
            ['time_process', 'desc'],
            ['bar_macconkey', 'asc'],
        ],
        'joins' => [
            ['ref_person as p', 'p.id_person', 't.id_person'],
        ],
        'columns' => [
            'bar_macconkey' => ['label' => 'Barcode macconkey'],
            'date_process'  => ['label' => 'Date process'],
            'time_process'  => ['label' => 'Time process'],
            'initial'       => ['label' => 'Lab tech', 'sql' => 'p.initial'],
            'bar_macsweep1' => ['label' => 'Macconkey sweep-1'],
            'cryobox1'      => ['label' => 'Cryobox-1'],
            'bar_macsweep2' => ['label' => 'Macconkey sweep-2'],
            'cryobox2'      => ['label' => 'Cryobox-2'],
            'comments'      => ['label' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
        // CSV: tambah lokasi freezer sweep-1 dan sweep-2 (lab terpilih)
        'export' => [
            'order' => [
                ['date_process', 'desc'],
                ['time_process', 'desc'],
                ['bar_macconkey', 'asc'],
            ],
            'joins' => [
                ['ref_person as p', 'p.id_person', 't.id_person'],
                ['freezer_in as g', 'g.id', 't.id_freezer1', ['g.lab = t.lab']],
                ['ref_location_80 as fr1', 'fr1.id_location_80', 'g.id_location_80', ['fr1.lab = t.lab']],
                ['freezer_in as h', 'h.id', 't.id_freezer2', ['h.lab = t.lab']],
                ['ref_location_80 as fr2', 'fr2.id_location_80', 'h.id_location_80', ['fr2.lab = t.lab']],
            ],
            'columns' => [
                'bar_macconkey' => ['label' => 'Barcode macconkey', 'csv' => 'Barcode_macconkey', 'sql' => 't.bar_macconkey'],
                'date_process'  => ['label' => 'Date process', 'csv' => 'Date_process', 'sql' => 't.date_process'],
                'time_process'  => ['label' => 'Time process', 'csv' => 'Time_process', 'sql' => 't.time_process'],
                'initial'       => ['label' => 'Lab tech', 'csv' => 'Lab_tech', 'sql' => 'p.initial'],
                'bar_macsweep1' => ['label' => 'Macconkey sweep-1', 'csv' => 'Barcode_mac_sweep1', 'sql' => 't.bar_macsweep1'],
                'cryobox1'      => ['label' => 'Cryobox-1', 'csv' => 'Cryobox1', 'sql' => 't.cryobox1'],
                'mac2_location1'=> ['label' => 'Location 1', 'csv' => 'Mac_sweep_Location1',
                                    'sql' => "CONCAT('F', fr1.freezer, '-S', fr1.shelf, '-R', fr1.rack, '-DRW', fr1.rack_level)"],
                'bar_macsweep2' => ['label' => 'Macconkey sweep-2', 'csv' => 'Barcode_mac_sweep2', 'sql' => 't.bar_macsweep2'],
                'cryobox2'      => ['label' => 'Cryobox-2', 'csv' => 'Cryobox2', 'sql' => 't.cryobox2'],
                'mac2_location2'=> ['label' => 'Location 2', 'csv' => 'Mac_sweep_Location2',
                                    'sql' => "CONCAT('F', fr2.freezer, '-S', fr2.shelf, '-R', fr2.rack, '-DRW', fr2.rack_level)"],
                'comments'      => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
            ],
        ],
    ],

    // ------------------------------------------------------------ Metagenomics (B/H/DW)
    // Freezer Location tampil di tabel, jadi join freezer_in dan ref_location_80 ada di definisi utama.
    'nhmrc_metagenomics_br' => [
        'title' => 'Metagenomics (B/H/DW)',
        'table' => 'nhmrc_metagenomics',
        'order' => [
            ['date_conduct', 'desc'],
            ['time_started', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'joins' => [
            ['freezer_in as g', 'g.id', 't.id_freezer', ['g.lab = t.lab']],
            ['ref_location_80 as b', 'b.id_location_80', 'g.id_location_80', ['b.lab = t.lab']],
        ],
        'columns' => [
            'barcode_sample'  => ['label' => 'Barcode (W0/S1)', 'csv' => 'Barcode_(W0/S1)'],
            'barcode_falcon2' => ['label' => 'Barcode falcon 2', 'csv' => 'Barcode_falcon2'],
            'date_conduct'    => ['label' => 'Date conducted', 'csv' => 'Date_conduct'],
            'volume_filtered' => ['label' => 'Volume filtered (mL)', 'csv' => 'Volume_filtered'],
            'time_started'    => ['label' => 'Time started filtering', 'csv' => 'Time_started', 'sql' => "DATE_FORMAT(t.time_started, '%H:%i')"],
            'time_finished'   => ['label' => 'Time finished filtering', 'csv' => 'Time_finished', 'sql' => "DATE_FORMAT(t.time_finished, '%H:%i')"],
            'time_minutes'    => ['label' => 'Duration filtering (minutes)', 'csv' => 'Duration(minutes)'],
            'barcode_dna_bag' => ['label' => 'Barcode DNA storage', 'csv' => 'Barcode_DNA_bag'],
            'barcode_storage' => ['label' => 'Barcode storage box', 'csv' => 'Barcode_storage'],
            'location'        => ['label' => 'Freezer Location', 'csv' => 'Location',
                                  'sql' => "CONCAT('F', b.freezer, '-S', b.shelf, '-R', b.rack, '-DRW', b.rack_level)"],
            'comments'        => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // ------------------------------------------------------------- Metagenomics (Food)
    'nhmrc_metagenomics_food' => [
        'title' => 'Metagenomics (Food)',
        'table' => 'nhmrc_meta_food',
        'order' => [
            ['date_conduct', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'columns' => [
            'barcode_sample'   => ['label' => 'Barcode sample'],
            'date_conduct'     => ['label' => 'Date conducted'],
            'barcode_dna1'     => ['label' => 'Barcode DNA tube1'],
            'barcode_storage1' => ['label' => 'Barcode BOX tube1'],
            'position_tube1'   => ['label' => 'Position tube1'],
            'barcode_dna2'     => ['label' => 'Barcode DNA tube2'],
            'barcode_storage2' => ['label' => 'Barcode BOX tube2'],
            'position_tube2'   => ['label' => 'Position tube2'],
            'comments'         => ['label' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
        // CSV: tambah berat sub-sample dan lokasi freezer tube1/tube2 (lab terpilih)
        'export' => [
            'order' => [
                ['date_conduct', 'desc'],
                ['barcode_sample', 'asc'],
            ],
            'joins' => [
                ['freezer_in as g', 'g.id', 't.id_freezer1', ['g.lab = t.lab']],
                ['ref_location_80 as b', 'b.id_location_80', 'g.id_location_80', ['b.lab = t.lab']],
                ['freezer_in as h', 'h.id', 't.id_freezer2', ['h.lab = t.lab']],
                ['ref_location_80 as c', 'c.id_location_80', 'h.id_location_80', ['c.lab = t.lab']],
            ],
            'columns' => [
                'barcode_sample'   => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample', 'sql' => 't.barcode_sample'],
                'date_conduct'     => ['label' => 'Date conducted', 'csv' => 'Date_conduct', 'sql' => 't.date_conduct'],
                'barcode_dna1'     => ['label' => 'Barcode DNA tube1', 'csv' => 'Barcode_dna_tube1', 'sql' => 't.barcode_dna1'],
                'weight_sub1'      => ['label' => 'Weight tube1', 'csv' => 'Weight_tube1', 'sql' => 't.weight_sub1'],
                'barcode_storage1' => ['label' => 'Barcode BOX tube1', 'csv' => 'Barcode_box1', 'sql' => 't.barcode_storage1'],
                'position_tube1'   => ['label' => 'Position tube1', 'csv' => 'Position_tube1', 'sql' => 't.position_tube1'],
                'location_tube1'   => ['label' => 'Location tube1', 'csv' => 'Location_tube1',
                                       'sql' => "CONCAT('F', b.freezer, '-S', b.shelf, '-R', b.rack, '-DRW', b.rack_level)"],
                'barcode_dna2'     => ['label' => 'Barcode DNA tube2', 'csv' => 'Barcode_dna_tube2', 'sql' => 't.barcode_dna2'],
                'weight_sub2'      => ['label' => 'Weight tube2', 'csv' => 'Weight_tube2', 'sql' => 't.weight_sub2'],
                'barcode_storage2' => ['label' => 'Barcode BOX tube2', 'csv' => 'Barcode_box2', 'sql' => 't.barcode_storage2'],
                'position_tube2'   => ['label' => 'Position tube2', 'csv' => 'Position_tube2', 'sql' => 't.position_tube2'],
                'location_tube2'   => ['label' => 'Location tube2', 'csv' => 'Location_tube2',
                                       'sql' => "CONCAT('F', c.freezer, '-S', c.shelf, '-R', c.rack, '-DRW', c.rack_level)"],
                'comments'         => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
            ],
        ],
    ],    

/*
 * NHMRC batch 3 / terakhir (5 modul): tempel di dalam array return config/modules.php
 * (jangan salin baris <?php dan komentar ini).
 *
 * Aturan: lab = lab terpilih dan flag = 0 otomatis; tanggal/jam DESC, lainnya ASC;
 * 'export_only' => kolom hanya muncul di CSV; urutan kolom CSV = urutan definisi.
 */

    // ------------------------------------------------------------------ Moisture Initial
    'nhmrc_moisture_initial' => [
        'title' => 'Moisture Initial',
        'table' => 'nhmrc_moisture1',
        'order' => [
            ['date_moisture', 'desc'],
            ['time_filter_start', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'columns' => [
            'barcode_sample'     => ['label' => 'Barcode (D0/S1)', 'csv' => 'Barcode_(D0/S1)'],
            'barcode_falcon2'    => ['label' => 'Barcode falcon 2', 'csv' => 'Barcode_falcon2'],
            'date_moisture'      => ['label' => 'Date moisture', 'csv' => 'Date_moisture'],
            'barcode_bootsock'   => ['label' => 'Barcode bootsock', 'csv' => 'Barcode_bootsock', 'export_only' => true],
            'barcode_foil'       => ['label' => 'Barcode foil/tray', 'csv' => 'Barcode_foil'],
            'foil_weight'        => ['label' => 'Foil/tray weight(g)', 'csv' => 'Foil_weight(g)'],
            'time_filter_start'  => ['label' => 'Time started filtering', 'csv' => 'Time_start_filter'],
            'time_filter_finish' => ['label' => 'Time finished filtering', 'csv' => 'Time_finish_filter'],
            'wet_weight'         => ['label' => 'Wet weight foil+sample(g)', 'csv' => 'Wet_weight(foil+sample)'],
            'time_incubator'     => ['label' => 'Time in incubator', 'csv' => 'Time_incubator'],
            'comments'           => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // -------------------------------------------------------------------- Moisture 24h
    'nhmrc_moisture_24' => [
        'title' => 'Moisture 24h',
        'table' => 'nhmrc_moisture2',
        'order' => [
            ['date_moisture', 'desc'],
            ['barcode_foil', 'asc'],
        ],
        'columns' => [
            'barcode_foil'  => ['label' => 'Barcode foil', 'csv' => 'Barcode_foil'],
            'date_moisture' => ['label' => 'Date moisture', 'csv' => 'Date_moisture'],
            'dry_weight24'  => ['label' => 'Dry weight 24h(g)', 'csv' => 'Dry_weight(24h)'],
            'comments'      => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // -------------------------------------------------------------------- Moisture 48h
    'nhmrc_moisture_48' => [
        'title' => 'Moisture 48h',
        'table' => 'nhmrc_moisture3',
        'order' => [
            ['date_moisture', 'desc'],
            ['barcode_foil', 'asc'],
        ],
        'columns' => [
            'barcode_foil'  => ['label' => 'Barcode foil', 'csv' => 'Barcode_foil'],
            'date_moisture' => ['label' => 'Date moisture', 'csv' => 'Date_moisture'],
            'dry_weight48'  => ['label' => 'Dry weight 48h(g)', 'csv' => 'Dry_weight(48h)'],
            'difference'    => ['label' => 'Difference 24 and 48h', 'csv' => 'Difference(24n48)'],
            'comments'      => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // -------------------------------------------------------------------- Moisture 72h
    'nhmrc_moisture_72' => [
        'title' => 'Moisture 72h',
        'table' => 'nhmrc_moisture4',
        'order' => [
            ['date_moisture', 'desc'],
            ['barcode_foil', 'asc'],
        ],
        'columns' => [
            'barcode_foil'  => ['label' => 'Barcode foil', 'csv' => 'Barcode_foil'],
            'date_moisture' => ['label' => 'Date moisture', 'csv' => 'Date_moisture'],
            'dry_weight72'  => ['label' => 'Dry weight 72h(g)', 'csv' => 'Dry_weight(72h)'],
            'comments'      => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],

    // ------------------------------------------------------------------ Sample Entry (HR)
    // Freezer Location tampil di tabel: freezer_in dan ref_location_80 sama-sama difilter lab.
    'nhmrc_sample_entry' => [
        'title' => 'Sample Entry (HR)',
        'table' => 'nhmrc_sample_entry',
        'order' => [
            ['date_conduct', 'desc'],
            ['barcode_sample', 'asc'],
            ['barcode_tube', 'asc'],
        ],
        'joins' => [
            ['freezer_in as c', 'c.id', 't.id_freezer', ['c.lab = t.lab']],
            ['ref_location_80 as b', 'b.id_location_80', 'c.id_location_80', ['b.lab = t.lab']],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'barcode_tube'   => ['label' => 'Barcode tube', 'csv' => 'Barcode_tube'],
            'date_conduct'   => ['label' => 'Date conducted', 'csv' => 'Date_conduct'],
            'vol_aliquot'    => ['label' => 'Volume aliquot', 'csv' => 'Volume_aliquot', 'export_only' => true],
            'barcode_box'    => ['label' => 'Barcode Box', 'csv' => 'Barcode_box'],
            'position_tube'  => ['label' => 'Position tube', 'csv' => 'Position_tube'],
            'location'       => ['label' => 'Freezer Location', 'csv' => 'Freezer_Location',
                                 'sql' => "CONCAT('F', b.freezer, '-S', b.shelf, '-R', b.rack, '-DRW', b.rack_level)"],
            'comments'       => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],


    // ------------------------------------------------------------------- DNA Sample
    'ref_allsample' => [
        'title'      => 'DNA Sample',
        'table'      => 'ref_sample',
        'lab_column' => null,
        'order' => [
            ['id_sample', 'asc'],
        ],
        'columns' => [
            'id_sample' => ['label' => 'ID', 'csv' => 'ID_DNA_sample'],
            'sample'    => ['label' => 'DNA sample', 'csv' => 'DNA_sample'],
        ],
    ],
 
    // ----------------------------------------------------------------- DNA Control
    'ref_dnacontrol' => [
        'title'      => 'DNA Control',
        'table'      => 'ref_sampledna',
        'lab_column' => null,
        'order' => [
            ['id_sample', 'asc'],
        ],
        'columns' => [
            'id_sample' => ['label' => 'ID', 'csv' => 'ID_DNA_sample'],
            'sample'    => ['label' => 'DNA sample', 'csv' => 'DNA_sample'],
        ],
    ],
 
    // ----------------------------------------------------------------- Destination
    'ref_destination' => [
        'title'      => 'Destination',
        'table'      => 'ref_destination',
        'lab_column' => null,
        'order' => [
            ['id_destination', 'asc'],
        ],
        'columns' => [
            'id_destination' => ['label' => 'ID', 'csv' => 'ID_destination'],
            'destination'    => ['label' => 'Destination', 'csv' => 'Destination'],
        ],
    ],
 
    // ------------------------------------------------------------------- Objective
    'ref_objective' => [
        'title' => 'Objective',
        'table' => 'ref_objective',
        'order' => [
            ['id_objective', 'asc'],
        ],
        'columns' => [
            'id_objective' => ['label' => 'ID', 'csv' => 'ID_objective'],
            'objective'    => ['label' => 'Objective', 'csv' => 'Objective'],
        ],
    ],
 
    // ------------------------------------------------------------ Freezer Location
    'ref_location' => [
        'title' => 'Freezer Location',
        'table' => 'ref_location_80',
        'order' => [
            ['id_location_80', 'asc'],
        ],
        'columns' => [
            'id_location_80' => ['label' => 'ID', 'csv' => 'ID'],
            'freezer'        => ['label' => 'Freezer', 'csv' => 'Freezer'],
            'shelf'          => ['label' => 'Shelf', 'csv' => 'Shelf'],
            'rack'           => ['label' => 'Rack', 'csv' => 'Rack'],
            'rack_level'     => ['label' => 'Drawer', 'csv' => 'Drawer'],
        ],
    ],
 
    // ----------------------------------------------------------------- Consumables
    'ref_consumables' => [
        'title' => 'Consumables',
        'table' => 'ref_consumables',
        'order' => [
            ['id_consumables', 'asc'],
        ],
        'joins' => [
            // objective dan stock bersifat lab-specific: join difilter lab
            ['ref_objective as ro', 'ro.id_objective', 't.id_objective', ['ro.lab = t.lab']],
            ['consumables_stock as cs', 'cs.id_stock', 't.id_stock', ['cs.lab = t.lab']],
        ],
        'columns' => [
            'id_consumables' => ['label' => 'ID', 'csv' => 'ID_consumables'],
            'objective'      => ['label' => 'Objective', 'csv' => 'Objective', 'sql' => 'ro.objective'],
            'product_name'   => ['label' => 'Product name', 'csv' => 'Product_name', 'sql' => 'cs.product_name'],
        ],
    ],


    // -------------------------------------------------------------------------- Person
    'ref_person' => [
        'title'      => 'Person',
        'table'      => 'ref_person',
        'lab_column' => null,
        'order' => [
            ['id_person', 'asc'],
        ],
        'columns' => [
            'id_person' => ['label' => 'ID', 'csv' => 'ID_person'],
            'realname'  => ['label' => 'Real name', 'csv' => 'Real_name'],
            'initial'   => ['label' => 'Initial', 'csv' => 'Initial'],
            'position'  => ['label' => 'Position', 'csv' => 'Position'],
        ],
    ],
 
    // ----------------------------------------------------------------------- Sample Type
    'ref_sampletype' => [
        'title'      => 'Sample Type',
        'table'      => 'ref_sampletype',
        'lab_column' => null,
        'order' => [
            ['id_sampletype', 'asc'],
        ],
        'columns' => [
            'id_sampletype' => ['label' => 'ID', 'csv' => 'ID_sampletype'],
            'sampletype'    => ['label' => 'Sample type', 'csv' => 'Sample_type'],
            'obj'           => ['label' => 'Objective', 'csv' => 'Objective'],
        ],
    ],
 
    // ------------------------------------------------------------------------- Shipping
    'ref_shipping' => [
        'title'      => 'Shipping',
        'table'      => 'ref_shipping',
        'lab_column' => null,
        'order' => [
            ['id_shipping', 'asc'],
        ],
        'columns' => [
            'id_shipping'     => ['label' => 'ID', 'csv' => 'ID_shipping'],
            'shipping_method' => ['label' => 'Shipping method', 'csv' => 'Shipping_method'],
        ],
    ],
 
    // ------------------------------------------------------------- Unprocessed Sample
    'ref_unproc_sample' => [
        'title' => 'Unprocessed Sample',
        'table' => 'unproc_sample',
        'order' => [
            ['date_entry', 'desc'],
            ['time_entry', 'desc'],
            ['barcode_sample', 'asc'],
        ],
        'columns' => [
            'barcode_sample' => ['label' => 'Barcode sample', 'csv' => 'Barcode_sample'],
            'date_entry'     => ['label' => 'Date entry', 'csv' => 'Date_entry'],
            'time_entry'     => ['label' => 'Time entry', 'csv' => 'Time_entry'],
            'comments'       => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
    ],
 
    // -------------------------------------------------------------------------- Vessel
    'ref_vessel' => [
        'title'      => 'Vessel',
        'table'      => 'ref_vessel',
        'lab_column' => null,
        'order' => [
            ['id_vessel', 'asc'],
        ],
        'columns' => [
            'id_vessel' => ['label' => 'ID', 'csv' => 'ID_vessel'],
            'vessel'    => ['label' => 'Vessel', 'csv' => 'Vessel'],
        ],
    ],


    // ------------------------------------------------------------------- Dictionary
    // Data global (tabel dictionary tidak difilter lab di LIMS lama).
    // Child row (+ di kolom ID): Sub dictionary (dictionary_det) dan Restriction.
    // Export Excel 2 sheet: Dictionary dan Sub_Dictionary (restriction tidak diexport).
    'dictionary' => [
        'title'        => 'Dictionary',
        'table'        => 'dictionary',
        'lab_column'   => null,
        'flag_column' => null,
        'export_format'=> 'xlsx',
        'order' => [
            ['module', 'asc'],
            ['id', 'asc'],
        ],
        'columns' => [
            'id'          => ['label' => 'ID'],
            'module'      => ['label' => 'Module'],
            'subheadings' => ['label' => 'Subheadings'],
            'var_label'   => ['label' => 'Variable Label'],
            'start_date'  => ['label' => 'Start Date'],
            'end_date'    => ['label' => 'End Date'],
            'detail'      => ['label' => 'Detail', 'sql' => "IF(t.dictionary_id IS NOT NULL, 'Yes', 'No')"],
            'format'      => ['label' => 'Format'],
            'description' => ['label' => 'Description', 'class' => 'col-trunc'],
            'restriction' => ['label' => 'Restriction',
                              'sql' => "IF(EXISTS(SELECT 1 FROM dictionary_restriction r WHERE r.restriction_id = t.id), 'Yes', '')"],
        ],
        'detail' => [
            'id_column' => 'id',
            'sections'  => [
                [
                    'title'       => 'Sub dictionary',
                    'table'       => 'dictionary_det',
                    'key'         => 'd.dictionary_id',
                    'parent'      => 't.dictionary_id',
                    'match'       => 't.id',
                    'lab_column'  => null,
                    'flag_column' => null,
                    'order'       => 'd.id ASC',
                    'columns'     => [
                        'factor_value' => ['label' => 'Factor value'],
                        'factor_label' => ['label' => 'Factor label'],
                        'start_date'   => ['label' => 'Start date'],
                        'end_date'     => ['label' => 'End date'],
                        'comments'     => ['label' => 'Comments', 'sql' => 'TRIM(d.comments)'],
                    ],
                ],
                [
                    'title'       => 'Restriction',
                    'table'       => 'dictionary_restriction',
                    'key'         => 'd.restriction_id',
                    'parent'      => 't.id',
                    'lab_column'  => null,
                    'flag_column' => null,
                    'order'       => 'd.restriction_id ASC',
                    'columns'     => [
                        'new_restriction_barcode'    => ['label' => 'Restriction barcode'],
                        'restriction_barcode_exists' => ['label' => 'Restriction barcode exists'],
                    ],
                ],
            ],
        ],
        'sheets' => [
            [
                'name'  => 'Dictionary',
                'order' => [
                    ['module', 'asc'],
                    ['id', 'asc'],
                ],
                'columns' => [
                    'id'            => ['label' => 'ID', 'csv' => 'ID', 'sql' => 't.id'],
                    'module'        => ['label' => 'Module', 'csv' => 'Module', 'sql' => 't.module'],
                    'subheadings'   => ['label' => 'Subheadings', 'csv' => 'Subheadings', 'sql' => 't.subheadings'],
                    'col_name'      => ['label' => 'Column name', 'csv' => 'Column_name', 'sql' => 't.col_name'],
                    'var_label'     => ['label' => 'Variable label', 'csv' => 'Variable_label', 'sql' => 't.var_label'],
                    'var_report'    => ['label' => 'Variable report', 'csv' => 'Variable_report', 'sql' => 't.var_report'],
                    'var_type'      => ['label' => 'Variable type', 'csv' => 'Variable_type', 'sql' => 't.var_type'],
                    'format'        => ['label' => 'Format', 'csv' => 'Format', 'sql' => 't.format'],
                    'description'   => ['label' => 'Description', 'csv' => 'Description', 'sql' => 't.description'],
                    'dictionary_id' => ['label' => 'Sub dictionary id', 'csv' => 'Sub_dictionary_id', 'sql' => 't.dictionary_id'],
                    'start_date'    => ['label' => 'Start date', 'csv' => 'Start_date', 'sql' => 't.start_date'],
                    'end_date'      => ['label' => 'End date', 'csv' => 'End_date', 'sql' => 't.end_date'],
                    'comments'      => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 't.comments'],
                ],
            ],
            [
                'name'        => 'Sub_Dictionary',
                'table'       => 'dictionary_det',
                'lab_column'  => null,
                'flag_column' => null,
                'order' => [
                    ['dictionary_id', 'asc'],
                    ['factor_value', 'asc'],
                ],
                'columns' => [
                    'dictionary_id' => ['label' => 'Sub dictionary id', 'csv' => 'Sub_dictionary_id', 'sql' => 't.dictionary_id'],
                    'factor_value'  => ['label' => 'Factor value', 'csv' => 'Factor_value', 'sql' => 't.factor_value'],
                    'factor_label'  => ['label' => 'Factor label', 'csv' => 'Factor_label', 'sql' => 't.factor_label'],
                    'start_date'    => ['label' => 'Start date', 'csv' => 'Start_date', 'sql' => 't.start_date'],
                    'end_date'      => ['label' => 'End date', 'csv' => 'End_date', 'sql' => 't.end_date'],
                    'comments'      => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 't.comments'],
                ],
            ],
        ],
    ],
 
    // ------------------------------------------------------------------------ Menu
    // Key harus sama dengan url menu ini di tbl_menu (di LIMS lama: kelolamenu).
    // tbl_menu tidak punya kolom lab/flag. Kolom "No" = id_menu (is_main_menu
    // merujuk ke id_menu parent). Is Aktif ditampilkan apa adanya (y/n).
    'kelolamenu' => [
        'title'       => 'Menu',
        'table'       => 'tbl_menu',
        'lab_column'  => null,
        'flag_column' => null,
        'order' => [
            ['id_menu', 'asc'],
        ],
        'columns' => [
            'id_menu'      => ['label' => 'No', 'csv' => 'No'],
            'title'        => ['label' => 'Title', 'csv' => 'Title'],
            'url'          => ['label' => 'Url', 'csv' => 'Url'],
            'icon'         => ['label' => 'Icon', 'csv' => 'Icon'],
            'is_main_menu' => ['label' => 'Is Main Menu', 'csv' => 'Is_Main_Menu'],
            'is_aktif'     => ['label' => 'Is Aktif', 'csv' => 'Is_Aktif'],
        ],
    ],

'user'      => ['title' => 'User Settings', 'custom' => true],
'userlevel' => ['title' => 'User Level',    'custom' => true],


    // ------------------------------------------------------- Lab Consumables - In Stock
    'consumables_stock_used' => [
        'title'         => 'Lab Consumables - In Stock',
        'table'         => 'consumables_stock',
        'export_format' => 'xlsx',
        'order' => [
            ['date_collected', 'desc'],
            ['id_stock', 'asc'],
        ],
        'columns' => [
            'id_stock'          => ['label' => 'ID In Stock'],
            'product_name'      => ['label' => 'Product Name'],
            'quantity'          => ['label' => 'Quantity'],
            'quantity_take'     => ['label' => 'Total Quantity Used'],
            'unit'              => ['label' => 'Unit'],
            'quantity_per_unit' => ['label' => 'Quantity Per Unit'],
            'unit_of_measure'   => ['label' => 'Unit of Measure'],
            'minimum_stock'     => ['label' => 'Minimum Stock'],
            'item_description'  => ['label' => 'Item Description', 'class' => 'col-trunc'],
            'date_collected'    => ['label' => 'Date Collected'],
            'comments'          => ['label' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
        'sheets' => [
            [
                'name'  => 'In Stock',
                'order' => [
                    ['date_collected', 'desc'],
                    ['product_name', 'asc'],
                ],
                'columns' => [
                    'product_name'      => ['label' => 'Product Name', 'csv' => 'Product Name', 'sql' => 't.product_name'],
                    'quantity'          => ['label' => 'Quantity', 'csv' => 'Quantity', 'sql' => 't.quantity'],
                    'unit'              => ['label' => 'Unit', 'csv' => 'Unit', 'sql' => 't.unit'],
                    'quantity_per_unit' => ['label' => 'Quantity Per Unit', 'csv' => 'Quantity Per Unit', 'sql' => 't.quantity_per_unit'],
                    'unit_of_measure'   => ['label' => 'Unit of Measure', 'csv' => 'Unit of Measure', 'sql' => 't.unit_of_measure'],
                    'item_description'  => ['label' => 'Item Description', 'csv' => 'Item Description', 'sql' => 't.item_description'],
                    'comments'          => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
                    'date_collected'    => ['label' => 'Date Collected', 'csv' => 'Date Collected', 'sql' => 't.date_collected'],
                ],
            ],
        ],
    ],
 
    // -------------------------------------------------------- Lab Consumables - Stock Take
    'consumables_in_stock' => [
        'title'         => 'Lab Consumables - Stock Take',
        'table'         => 'consumables_in_stock',
        'export_format' => 'xlsx',
        'order' => [
            ['date_collected', 'desc'],
            ['time_collected', 'desc'],
            ['id_instock', 'asc'],
        ],
        'joins' => [
            ['ref_objective as ro', 'ro.id_objective', 't.id_objective', ['ro.lab = t.lab']],
            ['consumables_stock as cs', 'cs.id_stock', 't.id_stock', ['cs.lab = t.lab']],
        ],
        'columns' => [
            'id_instock'        => ['label' => 'ID stock take'],
            'objective'         => ['label' => 'Objective', 'sql' => 'ro.objective'],
            'product_name'      => ['label' => 'Product name', 'sql' => 'cs.product_name'],
            'closed_container'  => ['label' => 'Number of closed containers'],
            'quantity_take'     => ['label' => 'Quantity Used'],
            'unit_measure_lab'  => ['label' => 'Unit of measure counted in the lab'],
            'quantity_per_unit' => ['label' => 'Quantity measured per unit'],
            'unit_of_measure'   => ['label' => 'Unit of measure'],
            'loose_items'       => ['label' => 'Number of loose items'],
            'total_quantity'    => ['label' => 'Total quantity', 'sql' => "CONCAT_WS(' ', t.total_quantity, t.unit_of_measure)"],
            'date_collected'    => ['label' => 'Collection date'],
            'time_collected'    => ['label' => 'Collection time'],
            'comments'          => ['label' => 'Comments', 'sql' => 'TRIM(t.comments)', 'class' => 'col-trunc'],
        ],
        'sheets' => [
            [
                'name'  => 'Stock Take',
                'order' => [
                    ['date_collected', 'desc'],
                    ['time_collected', 'desc'],
                    ['objective', 'asc'],
                ],
                'joins' => [
                    ['ref_objective as ro', 'ro.id_objective', 't.id_objective', ['ro.lab = t.lab']],
                    ['consumables_stock as cs', 'cs.id_stock', 't.id_stock', ['cs.lab = t.lab']],
                ],
                'columns' => [
                    'objective'         => ['label' => 'Objective', 'csv' => 'Objective', 'sql' => 'ro.objective'],
                    'product_name'      => ['label' => 'Product Name', 'csv' => 'Product Name', 'sql' => 'cs.product_name'],
                    'closed_container'  => ['label' => 'Close Container', 'csv' => 'Close Container', 'sql' => 't.closed_container'],
                    'unit_measure_lab'  => ['label' => 'Unit Measure Lab', 'csv' => 'Unit Measure Lab', 'sql' => 't.unit_measure_lab'],
                    'quantity_per_unit' => ['label' => 'Quantity Per Unit', 'csv' => 'Quantity Per Unit', 'sql' => 't.quantity_per_unit'],
                    'loose_items'       => ['label' => 'Loose Items', 'csv' => 'Loose Items', 'sql' => 't.loose_items'],
                    'total_quantity'    => ['label' => 'Total Quantity', 'csv' => 'Total Quantity', 'sql' => 't.total_quantity'],
                    'unit_of_measure'   => ['label' => 'Unit of Measure', 'csv' => 'Unit of Measure', 'sql' => 't.unit_of_measure'],
                    'expired_date'      => ['label' => 'Expired Date', 'csv' => 'Expired Date', 'sql' => 't.expired_date'],
                    'comments'          => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
                    'date_collected'    => ['label' => 'Date Collected', 'csv' => 'Date Collected', 'sql' => 't.date_collected'],
                    'time_collected'    => ['label' => 'Time Collected', 'csv' => 'Time Collected', 'sql' => 't.time_collected'],
                ],
            ],
        ],
    ],
 
    // ------------------------------------------------------------ Lab Consumables - Order
    // Received / Remaining / Status dihitung dari Order Detail yang belum dihapus (flag = 0).
    // Child row (+ di kolom ID Order): Order Detail. Export Excel 2 sheet: Orders dan Detail Received.
    'consumables_new_order' => [
        'title'         => 'Lab Consumables - Order',
        'table'         => 'consumables_order',
        'export_format' => 'xlsx',
        'order' => [
            ['date_ordered', 'desc'],
            ['id_order', 'asc'],
        ],
        'joins' => [
            ['consumables_stock as cs', 'cs.id_stock', 't.id_stock', ['cs.lab = t.lab']],
        ],
        'columns' => [
            'id_order'               => ['label' => 'ID Order'],
            'product_name'           => ['label' => 'Product Name', 'sql' => 'cs.product_name'],
            'quantity_ordering'      => ['label' => 'Order Qty'],
            'unit_ordering'          => ['label' => 'Unit Order'],
            'quantity_per_unit'      => ['label' => 'Qty Per Unit'],
            'unit_of_measure'        => ['label' => 'Unit Of Measure'],
            'total_quantity_ordered' => ['label' => 'Total Qty Order'],
            'vendor'                 => ['label' => 'Vendor'],
            'date_ordered'           => ['label' => 'Date Ordered'],
            'remaining_quantity'     => ['label' => 'Remaining Qty',
                'sql' => '(t.quantity_ordering - (SELECT COALESCE(SUM(d.amount_received), 0) FROM consumables_order_detail d WHERE d.id_order = t.id_order AND d.flag = 0))'],
            'received'               => ['label' => 'Qty Received',
                'sql' => '(SELECT COALESCE(SUM(d.amount_received), 0) FROM consumables_order_detail d WHERE d.id_order = t.id_order AND d.flag = 0)'],
            'status'                 => ['label' => 'Order Status',
                'sql' => "IF((SELECT COALESCE(SUM(d.amount_received), 0) FROM consumables_order_detail d WHERE d.id_order = t.id_order AND d.flag = 0) = t.quantity_ordering, 'Completed', 'Uncompleted')"],
            'note'                   => ['label' => 'Note', 'sql' => 'TRIM(t.note)', 'class' => 'col-trunc'],
        ],
        'detail' => [
            'id_column' => 'id_order',
            'title'     => 'Order Detail',
            'table'     => 'consumables_order_detail',
            'key'       => 'd.id_order',
            'parent'    => 't.id_order',
            'lab_column'=> null,   // consumables_order_detail tidak punya kolom lab
            'order'     => 'd.date_received DESC, d.time_received DESC, d.id_orderdetail ASC',
            'columns'   => [
                'id_orderdetail'            => ['label' => 'ID Order Detail'],
                'name_received'             => ['label' => 'Name Received'],
                'amount_received'           => ['label' => 'Amount Received'],
                'received'                  => ['label' => 'Status Received'],
                'unit_reference'            => ['label' => 'Unit Reference'],
                'date_received'             => ['label' => 'Date Received'],
                'time_received'             => ['label' => 'Time Received'],
                'contact_supplier_progress' => ['label' => 'Contact Supplier'],
                'comments'                  => ['label' => 'Comments', 'sql' => 'TRIM(d.comments)'],
            ],
        ],
        'sheets' => [
            [
                'name'  => 'Orders',
                'order' => [
                    ['date_ordered', 'desc'],
                    ['product_name', 'asc'],
                ],
                'joins' => [
                    ['consumables_stock as cs', 'cs.id_stock', 't.id_stock', ['cs.lab = t.lab']],
                ],
                'columns' => [
                    'product_name'           => ['label' => 'Product Name', 'csv' => 'Product Name', 'sql' => 'cs.product_name'],
                    'quantity_ordering'      => ['label' => 'Quantity Ordering', 'csv' => 'Quantity Ordering', 'sql' => 't.quantity_ordering'],
                    'unit_ordering'          => ['label' => 'Unit Ordering', 'csv' => 'Unit Ordering', 'sql' => 't.unit_ordering'],
                    'quantity_per_unit'      => ['label' => 'Quantity Per Unit', 'csv' => 'Quantity Per Unit', 'sql' => 't.quantity_per_unit'],
                    'total_quantity_ordered' => ['label' => 'Total Quantity Ordered', 'csv' => 'Total Quantity Ordered', 'sql' => 't.total_quantity_ordered'],
                    'unit_of_measure'        => ['label' => 'Unit of Measure', 'csv' => 'Unit of Measure', 'sql' => 't.unit_of_measure'],
                    'vendor'                 => ['label' => 'Vendor', 'csv' => 'Vendor', 'sql' => 't.vendor'],
                    'date_ordered'           => ['label' => 'Date Ordered', 'csv' => 'Date Ordered', 'sql' => 't.date_ordered'],
                    'time_ordered'           => ['label' => 'Time Ordered', 'csv' => 'Time Ordered', 'sql' => 't.time_ordered'],
                    'remaining_quantity'     => ['label' => 'Remaining Quantity', 'csv' => 'Remaining Quantity',
                        'sql' => '(t.quantity_ordering - (SELECT COALESCE(SUM(d.amount_received), 0) FROM consumables_order_detail d WHERE d.id_order = t.id_order AND d.flag = 0))'],
                    'received'               => ['label' => 'Received Quantity', 'csv' => 'Received Quantity',
                        'sql' => '(SELECT COALESCE(SUM(d.amount_received), 0) FROM consumables_order_detail d WHERE d.id_order = t.id_order AND d.flag = 0)'],
                    'status'                 => ['label' => 'Status', 'csv' => 'Status',
                        'sql' => "IF((SELECT COALESCE(SUM(d.amount_received), 0) FROM consumables_order_detail d WHERE d.id_order = t.id_order AND d.flag = 0) = t.quantity_ordering, 'Completed', 'Uncompleted')"],
                    'received_by'            => ['label' => 'Received By', 'csv' => 'Received By',
                        'sql' => "(SELECT GROUP_CONCAT(d.name_received SEPARATOR ', ') FROM consumables_order_detail d WHERE d.id_order = t.id_order AND d.flag = 0)"],
                ],
            ],
            [
                'name'        => 'Detail Received',
                'table'       => 'consumables_order_detail',
                'lab_column'  => null,
                'order' => [
                    ['date_received', 'desc'],
                    ['time_received', 'desc'],
                    ['name_received', 'asc'],
                ],
                'joins' => [
                    // hanya detail dari order milik lab terpilih
                    ['(SELECT * FROM consumables_order WHERE lab = {lab} AND flag = 0) as co', 'co.id_order', 't.id_order'],
                    ['consumables_stock as cs', 'cs.id_stock', 'co.id_stock', ['cs.lab = co.lab']],
                ],
                'where' => ['co.id_order IS NOT NULL'],
                'columns' => [
                    'name_received'             => ['label' => 'Received By', 'csv' => 'Received By', 'sql' => 't.name_received'],
                    'product_name'              => ['label' => 'Product Name', 'csv' => 'Product Name', 'sql' => 'cs.product_name'],
                    'amount_received'           => ['label' => 'Amount Received', 'csv' => 'Amount Received', 'sql' => 't.amount_received'],
                    'date_received'             => ['label' => 'Date Received', 'csv' => 'Date Received', 'sql' => 't.date_received'],
                    'time_received'             => ['label' => 'Time Received', 'csv' => 'Time Received', 'sql' => 't.time_received'],
                    'contact_supplier_progress' => ['label' => 'Contact Supplier', 'csv' => 'Contact Supplier', 'sql' => 't.contact_supplier_progress'],
                    'comments'                  => ['label' => 'Comments', 'csv' => 'Comments', 'sql' => 'TRIM(t.comments)'],
                ],
            ],
        ],
    ],
 
    // ----------------------------------------------------- Lab Consumables - Consumables
    // Modul lama (tabel consumables). Hapus blok ini kalau menunya sudah tidak dipakai.
    'consumables_product' => [
        'title'       => 'Lab Consumables - Consumables',
        'table'       => 'consumables',
        'lab_column'  => null,
        'order' => [
            ['date_collected', 'desc'],
            ['time_collected', 'desc'],
            ['id', 'asc'],
        ],
        'joins' => [
            ['consumables_stock as cs', 'cs.id_stock', 't.id_stock'],
        ],
        'columns' => [
            'id'               => ['label' => 'No'],
            'product_name'     => ['label' => 'Product Name', 'sql' => 'cs.product_name'],
            'quantity'         => ['label' => 'Quantity'],
            'unit_of_measure'  => ['label' => 'Unit of Measure'],
            'item_description' => ['label' => 'Item Description', 'class' => 'col-trunc'],
            'date_collected'   => ['label' => 'Date Collected'],
            'time_collected'   => ['label' => 'Time Collected'],
        ],
    ],
'consumables_report' => ['title' => 'Consumables - Report', 'custom' => true],
'rep_o3'    => ['title' => 'Reports - Objective 3',     'custom' => true],
'rep_o2a'   => ['title' => 'Reports - Objective 2A',    'custom' => true],
'rep_o2b'   => ['title' => 'Reports - Objective 2B',    'custom' => true],
'rep_dna'   => ['title' => 'Reports - DNA',             'custom' => true],
'rep_nhmrc' => ['title' => 'Reports - NHMRC',           'custom' => true],
'rep_frez'  => ['title' => 'Reports - Freezer',         'custom' => true],
'rep_se'    => ['title' => 'Reports - Sample External', 'custom' => true],

];