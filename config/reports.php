<?php
/*
 * config/reports.php: halaman Reports (dibuat dari controller dan model LIMS lama).
 * Placeholder SQL: {d1} {d2} = tanggal, {lab} = lab terpilih, {rep} = jenis sampel,
 * {where_rep} / {type_where} diisi dari konfigurasi 'types'. Semuanya dikirim sebagai binding PDO.
 */
return [

    // ============================================================
    'rep_o3' => [
        'title' => 'REPORTS - Objective 3 REPORT',
        'buttons' => [
            [
                'label' => 'Sample Reception',
                'module' => 'o3_sample_reception',
            ],
            [
                'label' => 'Blood-Centrifuge',
                'module' => 'o3_blood_centrifuge',
            ],
            [
                'label' => 'EDTA-Aliquots',
                'module' => 'o3_blood_edta',
            ],
            [
                'label' => 'SST-Aliquots',
                'module' => 'o3_blood_sst',
            ],
            [
                'label' => 'Filter Paper',
                'module' => 'o3_filter_paper',
            ],
            [
                'label' => 'KK 1',
                'module' => 'o3_feces_kk1',
            ],
            [
                'label' => 'Feces-Aliquots',
                'module' => 'o3_feces_aliquot',
            ],
            [
                'label' => 'Macconkey 1',
                'module' => 'o3_feces_mac1',
            ],
            [
                'label' => 'Macconkey 2',
                'module' => 'o3_feces_mac2',
            ],
            [
                'label' => 'KK 2',
                'module' => 'o3_feces_kk2',
            ],
        ],
        'filename' => 'ALL_O3_reports',
        'sheets' => [
            [
                'name' => 'Sample_reception',
                'sql' => <<<'SQL'
SELECT a.barcode_sample AS Barcode_sample, a.date_receipt AS Date_receipt, a.time_receipt AS Time_receipt,
b.initial AS Lab_tech, c.sampletype AS Sample_type, a.png_control AS PnG_control, a.cold_chain AS Cold_chain,
a.cont_intact AS Cont_intact, TRIM(a.comments) AS Comments
FROM obj3_sam_rec a
left join ref_person b on a.id_person = b.id_person
left join ref_sampletype c on a.id_type = c.id_sampletype
WHERE  (a.date_receipt >= {d1}
AND a.date_receipt <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_receipt, a.time_receipt ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_receipt', 'Time_receipt', 'Lab_tech', 'Sample_type', 'PnG_control', 'Cold_chain', 'Cont_intact', 'Comments'],
            ],
            [
                'name' => 'Blood_centrifuge',
                'sql' => <<<'SQL'
SELECT a.ID AS ID, a.date_process AS Date_process, c.initial AS Lab_tech,
a.centrifuge_time AS Centrifuge_time, TRIM(a.comments) AS Comments, b.barcode_sample AS Barcode_sample, b.comments AS Comments_sample
FROM obj3_blood_centrifuge a
LEFT JOIN obj3_blood_centrifuge_det b ON a.id = b.id_bc AND b.flag = 0 AND b.lab = {lab}
LEFT JOIN ref_person c ON a.id_person = c.id_person
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process ASC
SQL,
                'columns' => ['ID', 'Date_process', 'Lab_tech', 'Centrifuge_time', 'Comments', 'Barcode_sample', 'Comments_sample'],
            ],
            [
                'name' => 'EDTA_Aliquots',
                'sql' => <<<'SQL'
SELECT a.barcode_sample AS Barcode_sample, a.date_process AS Date_process, b.initial AS Lab_tech,
a.hemolysis AS Hemolysis, a.barcode_wb AS Barcode_Wholeblood, a.vol_aliquotwb AS Volume_wholeblood,
a.cryoboxwb AS Cryobox_wholeblood, a.barcode_p1a AS Barcode_plasma1, a.vol_aliquot1 AS Volume_aliquot1,
a.cryobox1 AS Cryobox1, a.barcode_p2a AS Barcode_plasma2, a.vol_aliquot2 AS Volume_aliquot2,
a.cryobox2 AS Cryobox2, a.barcode_p3a AS Barcode_plasma3, a.vol_aliquot3 AS Volume_aliquot3,
a.cryobox3 AS Cryobox3, a.packed_cells AS Packed_cells, a.cryobox_pc AS Cryobox_packed_cells,
TRIM(a.comments) AS Comments
from obj3_edta_aliquots a
left join ref_person b on a.id_person = b.id_person
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_process', 'Lab_tech', 'Hemolysis', 'Barcode_Wholeblood', 'Volume_wholeblood', 'Cryobox_wholeblood', 'Barcode_plasma1', 'Volume_aliquot1', 'Cryobox1', 'Barcode_plasma2', 'Volume_aliquot2', 'Cryobox2', 'Barcode_plasma3', 'Volume_aliquot3', 'Cryobox3', 'Packed_cells', 'Cryobox_packed_cells', 'Comments'],
            ],
            [
                'name' => 'SST_Aliquots',
                'sql' => <<<'SQL'
SELECT a.barcode_sample AS Barcode_sample, a.date_process AS Date_process, b.initial AS Lab_tech,
a.barcode_sst1 AS Barcode_SST1, a.vol_aliquot1 AS Vol_aliquot1, a.cryobox1 AS Cryobox1,
a.barcode_sst2 AS Barcode_SST2, a.vol_aliquot2 AS Vol_aliquot2, a.cryobox2 AS Cryobox2,
TRIM(a.comments) AS Comments
from obj3_sst_aliquots a
left join ref_person b on a.id_person = b.id_person
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_process', 'Lab_tech', 'Barcode_SST1', 'Vol_aliquot1', 'Cryobox1', 'Barcode_SST2', 'Vol_aliquot2', 'Cryobox2', 'Comments'],
            ],
            [
                'name' => 'Filter_Paper',
                'sql' => <<<'SQL'
SELECT a.barcode_sample AS Barcode_sample, a.date_process AS Date_process,
a.time_process AS Time_process, b.initial AS Lab_tech, a.freezer_bag AS Freezer_bag,
concat("F",c.freezer,"-","S",c.shelf,"-","R",c.rack,"-","DRW",c.rack_level) AS Freezer_location,
TRIM(a.comments) AS Comments
from obj3_bfilterpaper a
left join ref_person b on a.id_person = b.id_person
left join ref_location_80 c on a.id_location_80 = c.id_location_80 AND c.lab = {lab}
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process, a.time_process ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_process', 'Time_process', 'Lab_tech', 'Freezer_bag', 'Freezer_location', 'Comments'],
            ],
            [
                'name' => 'Feces_KK1',
                'sql' => <<<'SQL'
SELECT a.barcode_sample AS Barcode_sample, a.date_process AS Date_process,
a.time_process AS Time_process, b.initial AS Lab_tech, a.bar_kkslide AS Barcode_kkslide, TRIM(a.comments) AS Comments
from obj3_fkk1 a
left join ref_person b on a.id_person = b.id_person
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process, a.time_process ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_process', 'Time_process', 'Lab_tech', 'Barcode_kkslide', 'Comments'],
            ],
            [
                'name' => 'Feces_Aliquots',
                'sql' => <<<'SQL'
SELECT a.barcode_sample AS Barcode_sample, a.date_process AS Date_process,
a.time_process AS Time_process, b.initial AS Lab_tech, a.cons_stool AS Cons_stool,
a.color_stool AS Color_stool, a.abnormal AS Abnormal, a.ab_other AS Abnormal_note,
a.aliquot1 AS Aliquot1, a.volume1 AS Volume1, a.cryobox1 AS Cryobox1,
a.aliquot2 AS Aliquot2, a.volume2 AS Volume2, a.cryobox2 AS Cryobox2,
a.aliquot3 AS Aliquot3, a.volume3 AS Volume3, a.cryobox3 AS Cryobox3,
a.aliquot_zymo AS Aliquot_zymo, a.volume_zymo AS Volume_zymo,
a.batch_zymo AS Batch_zymo, a.cryobox_zymo AS Cryobox_zymo, TRIM(a.comments) AS Comments
from (obj3_faliquot a left join ref_person b on((a.id_person = b.id_person)))
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process, a.time_process ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_process', 'Time_process', 'Lab_tech', 'Cons_stool', 'Color_stool', 'Abnormal', 'Abnormal_note', 'Aliquot1', 'Volume1', 'Cryobox1', 'Aliquot2', 'Volume2', 'Cryobox2', 'Aliquot3', 'Volume3', 'Cryobox3', 'Aliquot_zymo', 'Volume_zymo', 'Batch_zymo', 'Cryobox_zymo', 'Comments'],
            ],
            [
                'name' => 'Feces_Macconkey1',
                'sql' => <<<'SQL'
SELECT a.barcode_sample AS Barcode_sample, a.date_process AS Date_process,
a.time_process AS Time_process, b.initial AS Lab_tech, a.bar_macconkey AS Barcode_macconkey,
TRIM(a.comments) AS Comments
from obj3_fmac1 a
left join ref_person b on a.id_person = b.id_person
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process, a.time_process ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_process', 'Time_process', 'Lab_tech', 'Barcode_macconkey', 'Comments'],
            ],
            [
                'name' => 'Feces_Macconkey2',
                'sql' => <<<'SQL'
SELECT a.bar_macconkey AS Barcode_macconkey, a.date_process AS Date_process,
a.time_process AS Time_process, b.initial AS Lab_tech,
a.bar_macsweep1 AS Barcode_macsweep1, a.cryobox1 AS Cryobox1,
a.bar_macsweep2 AS Barcode_macsweep2, a.cryobox2 AS Cryobox2, TRIM(a.comments) AS Comments
from obj3_fmac2 a
left join ref_person b on a.id_person = b.id_person
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process, a.time_process ASC
SQL,
                'columns' => ['Barcode_macconkey', 'Date_process', 'Time_process', 'Lab_tech', 'Barcode_macsweep1', 'Cryobox1', 'Barcode_macsweep2', 'Cryobox2', 'Comments'],
            ],
            [
                'name' => 'Feces_KK2',
                'sql' => <<<'SQL'
SELECT a.bar_kkslide AS Barcode_kkslide, a.date_process AS Date_process,
b.initial AS Person_Read, c.initial AS Person_Write, a.duration AS Duration,
a.start_time AS Start_time, a.end_time AS End_time,
a.ascaris AS AscarisLumbricoides, a.ascaris_com AS AscarisLumbricoides_comment,
a.hookworm AS Hookworm, a.hookworm_com AS Hookworm_comment,
a.trichuris AS TrichurisTrichura, a.trichuris_com AS TrichurisTrichura_comment,
a.strongyloides AS StrongyloidesStercoralis, a.strongyloides_com AS StrongyloidesStercoralis_comment,
a.taenia AS TaeniaSpss, a.taenia_com AS TaeniaSpss_comment,
a.other AS Other, a.other_com AS Other_comment, TRIM(a.comments) AS GeneralComments,
a.finalized AS Finalized
from obj3_fkk2 a
left join ref_person b on a.id_person = b.id_person
left join ref_person c on a.id_person2 = c.id_person
WHERE (a.date_process >= {d1}
AND a.date_process <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_process ASC
SQL,
                'columns' => ['Barcode_kkslide', 'Date_process', 'Person_Read', 'Person_Write', 'Duration', 'Start_time', 'End_time', 'AscarisLumbricoides', 'AscarisLumbricoides_comment', 'Hookworm', 'Hookworm_comment', 'TrichurisTrichura', 'TrichurisTrichura_comment', 'StrongyloidesStercoralis', 'StrongyloidesStercoralis_comment', 'TaeniaSpss', 'TaeniaSpss_comment', 'Other', 'Other_comment', 'GeneralComments', 'Finalized'],
            ],
        ],
        'preview' => [
            'columns' => [
                'barcode_sample' => 'Barcode sample',
                'date_receipt' => 'Date receipt',
                'time_receipt' => 'Time receipt',
                'initial' => 'Lab tech',
                'sample_type' => 'Sample type',
                'png_control' => 'P&G control',
                'cold_chain' => 'Cold chain',
                'cont_intact' => 'Cont. intact',
            ],
            'sql' => <<<'SQL'
SELECT a.barcode_sample, a.date_receipt, a.time_receipt, p.initial, st.sampletype AS sample_type,
       a.png_control, a.cold_chain, a.cont_intact
FROM obj3_sam_rec a
LEFT JOIN ref_person p ON a.id_person = p.id_person
LEFT JOIN ref_sampletype st ON a.id_type = st.id_sampletype
WHERE a.lab = {lab} AND a.flag = 0
  AND a.date_receipt >= {d1} AND a.date_receipt <= {d2}
ORDER BY a.date_receipt DESC, a.time_receipt DESC, a.barcode_sample ASC
LIMIT 20000
SQL,
        ],
    ],

    // ============================================================
    'rep_o2a' => [
        'title' => 'REPORTS - Objective 2A REPORT',
        'buttons' => [
            [
                'label' => 'Sample Reception',
                'module' => 'o2a_sample_reception',
            ],
            [
                'label' => 'Sample Logging',
                'module' => 'o2a_sample_logging',
            ],
            [
                'label' => 'Mosquito Identifications',
                'module' => 'o2a_mosquito_identifications',
            ],
        ],
        'filename' => 'ALL_O2A_reports',
        'sheets' => [
            [
                'name' => 'Sample_reception',
                'sql' => <<<'SQL'
SELECT b.barcode_sample AS Barcode_storagebag, a.date_receipt AS Date_receipt,
c.initial AS Delivered_by, d.initial AS Received_by, a.sample_type AS Sample_type
from obj2a_receipt a
LEFT JOIN obj2a_receipt_det b ON a.id_receipt = b.id_receipt AND b.lab = {lab}
LEFT JOIN ref_person c ON a.id_delivered = c.id_person
LEFT JOIN ref_person d ON a.id_received = d.id_person
WHERE  (a.date_receipt >= {d1}
AND a.date_receipt <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_receipt, A.id_receipt
SQL,
                'columns' => ['Barcode_storagebag', 'Date_receipt', 'Delivered_by', 'Received_by', 'Sample_type'],
            ],
            [
                'name' => 'Sample_logging',
                'sql' => <<<'SQL'
SELECT a.id_samplelog AS ID, a.date_collection AS Date_collection,
b.initial AS Lab_tech, a.bar_samplebag AS Barcode_samplebag,
a.bar_eclosion AS Barcode_eclosion, a.id_person,
a.lab, a.flag
from obj2a_samplelog a
left join ref_person b on a.id_person = b.id_person
WHERE (a.date_collection >= {d1}
AND a.date_collection <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_collection, a.id_samplelog ASC
SQL,
                'columns' => ['ID', 'Date_collection', 'Lab_tech', 'Barcode_samplebag', 'Barcode_eclosion'],
            ],
            [
                'name' => 'Mosquito_Identification',
                'sql' => <<<'SQL'
SELECT a.bar_storagebag AS Barcode_storagebag,
    b.initial AS Initial_Person,
    a.date_ident AS Date_identification,
    a.catch_met AS Catch_methode,
    a.no_mosquito AS SumMosquito,
    a.aedes_aegypt_male AS Aedes_aegypt_male,
    a.aedes_aegypt_female AS Aedes_aegypt_female,
    a.aedes_albopictus_male AS Aedes_albopictus_male,
    a.aedes_albopictus_female AS Aedes_albopictus_female,
    a.aedes_polynesiensis_male AS Aedes_polynesiensis_male,
    a.aedes_polynesiensis_female AS Aedes_polynesiensis_female,
    a.aedes_other_male AS Aedes_other_male,
    a.aedes_other_female AS Aedes_other_female,
    a.culex_male AS Culex_male,
    a.culex_female AS Culex_female,
    a.culex_sitiens_male AS Culex_sitiens_male,
    a.culex_sitiens_female AS Culex_sitiens_female,
    a.culexann_male AS Culexann_male,
    a.culexann_female AS Culexann_female,
    a.culex_other_male AS Culex_other_male,
    a.culex_other_female AS Culex_other_female,
    a.anopheles_male AS Anopheles_male,
    a.anopheles_female AS Anopheles_female,
    a.uranotaenia_male AS Uranotaenia_male,
    a.uranotaenia_female AS Uranotaenia_female,
    a.mansonia_male AS Mansonia_male,
    a.mansonia_female AS Mansonia_female,
    a.other_male AS Other_male,
    a.other_female AS Other_female,
    IFNULL(a.culex_larvae,0) AS Culex_larvae,
    IFNULL(a.aedes_larvae,0) AS Aedes_larvae,
    IFNULL(a.unidentify,0) AS Unidentify,
    TRIM(a.notes) AS Notes,
    a.id_person AS id_person,
    a.lab AS lab,
    a.flag AS flag
FROM
    obj2a_identification a
    LEFT JOIN ref_person b ON a.id_person = b.id_person
    WHERE (a.date_ident >= {d1}
    AND a.date_ident <= {d2})
    AND a.lab = {lab}
    AND a.flag = 0
    ORDER BY a.date_ident ASC
SQL,
                'columns' => ['Barcode_storagebag', 'Initial_Person', 'Date_identification', 'Catch_methode', 'SumMosquito', 'Aedes_aegypt_male', 'Aedes_aegypt_female', 'Aedes_albopictus_male', 'Aedes_albopictus_female', 'Aedes_polynesiensis_male', 'Aedes_polynesiensis_female', 'Aedes_other_male', 'Aedes_other_female', 'Culex_male', 'Culex_female', 'Culex_sitiens_male', 'Culex_sitiens_female', 'Culexann_male', 'Culexann_female', 'Culex_other_male', 'Culex_other_female', 'Anopheles_male', 'Anopheles_female', 'Uranotaenia_male', 'Uranotaenia_female', 'Mansonia_male', 'Mansonia_female', 'Other_male', 'Other_female', 'Culex_larvae', 'Aedes_larvae', 'Unidentify', 'Notes'],
            ],
        ],
        'preview' => [
            'columns' => [
                'id_receipt' => 'ID',
                'date_receipt' => 'Date receipt',
                'delivered' => 'Delivered',
                'received' => 'Received',
                'sample_type' => 'Sample type',
            ],
            'sql' => <<<'SQL'
SELECT a.id_receipt, a.date_receipt, b.initial AS delivered, c.initial AS received, a.sample_type
FROM obj2a_receipt a
LEFT JOIN ref_person b ON a.id_delivered = b.id_person
LEFT JOIN ref_person c ON a.id_received = c.id_person
WHERE a.lab = {lab} AND a.flag = 0
  AND a.date_receipt >= {d1} AND a.date_receipt <= {d2}
ORDER BY a.date_receipt DESC, a.id_receipt ASC
LIMIT 20000
SQL,
        ],
    ],

    // ============================================================
    'rep_o2b' => [
        'title' => 'REPORTS - Objective 2B REPORT',
        'buttons' => [
            [
                'label' => 'Reception',
                'module' => 'o2b_sample_reception',
            ],
            [
                'label' => 'B.Stomacher',
                'module' => 'o2b_bootsocks_stomacher',
            ],
            [
                'label' => 'B.Before',
                'module' => 'o2b_bootsocks_before',
            ],
            [
                'label' => 'B.After',
                'module' => 'o2b_bootsocks_after',
            ],
            [
                'label' => 'S.Sampleprep',
                'module' => 'o2b_sample_prep',
            ],
            [
                'label' => 'Other Lab',
                'module' => 'o2b_other_lab',
            ],
            [
                'label' => 'Blank IN',
                'module' => 'o2b_blank_in',
            ],
            [
                'label' => 'Endetec In(W)',
                'module' => 'o2b_endetec_in',
            ],
            [
                'label' => 'Endetec Out(W)',
                'module' => 'o2b_endetec_out_w',
            ],
            [
                'label' => 'Endetec Out(BS)',
                'module' => 'o2b_endetec_out_bs',
            ],
            [
                'label' => 'IDEXX In(W)',
                'module' => 'o2b_idexx_in',
            ],
            [
                'label' => 'IDEXX Out(All)',
                'module' => 'o2b_idexx_out',
            ],
            [
                'label' => 'Mac In',
                'module' => 'o2b_macconkey_in',
            ],
            [
                'label' => 'Mac Out',
                'module' => 'o2b_macconkey_out',
            ],
            [
                'label' => 'Meta (WB)',
                'module' => 'o2b_metagenomics_wb',
            ],
            [
                'label' => 'Meta (SF)',
                'module' => 'o2b_metagenomics_sf',
            ],
            [
                'label' => 'Mois (I)',
                'module' => 'o2b_moisture_initial',
            ],
            [
                'label' => 'Mois (24)',
                'module' => 'o2b_moisture_24',
            ],
            [
                'label' => 'Mois (48)',
                'module' => 'o2b_moisture_48',
            ],
            [
                'label' => 'Mois (72)',
                'module' => 'o2b_moisture_72',
            ],
        ],
        'type_label' => 'Report for sample type',
        'types' => [
            '6' => [
                'label' => 'Water',
                'query' => 'water',
                'where_rep' => ' AND (LEFT(a.barcode_sample, 2) <> \'N0\' AND LEFT(a.barcode_sample, 2) <> \'F0\')',
                'preview_where' => 'a.id_type2b = 6 AND (LEFT(a.barcode_sample, 2) <> \'N0\' AND LEFT(a.barcode_sample, 2) <> \'F0\')',
                'filename' => 'O2B_water_report',
            ],
            '6x' => [
                'label' => 'Water (Site-0)',
                'query' => 'water',
                'where_rep' => ' AND (LEFT(a.barcode_sample, 2) = \'N0\' OR LEFT(a.barcode_sample, 2) = \'F0\')',
                'preview_where' => 'a.id_type2b = 6 AND (LEFT(a.barcode_sample, 2) = \'N0\' OR LEFT(a.barcode_sample, 2) = \'F0\')',
                'filename' => 'O2B_water_site0_report',
            ],
            '9' => [
                'label' => 'Bootsocks',
                'query' => 'bootsock',
                'preview_where' => 'a.id_type2b = 9',
                'filename' => 'O2B_bootsock_report',
            ],
            '7' => [
                'label' => 'Sediment',
                'query' => 'sediment',
                'preview_where' => 'a.id_type2b = 7',
                'filename' => 'O2B_sediment_report',
            ],
            '8' => [
                'label' => 'Animal Feces',
                'query' => 'feces',
                'preview_where' => 'a.id_type2b = 8',
                'filename' => 'O2B_animalfeces_report',
            ],
            '6x2' => [
                'label' => 'Fecal Coliform',
                'query' => 'water',
                'where_rep' => ' AND e.comments LIKE \'%Faecal%\'',
                'preview_where' => 'a.id_type2b = 6 AND a.barcode_sample IN (SELECT barcode_sample FROM obj2b_idexx1 WHERE lab = {lab} AND comments LIKE \'%Faecal%\')',
                'filename' => 'O2B_wetland_faecal_coliform_report',
            ],
        ],
        'queries' => [
            'water' => [
                'sql' => <<<'SQL'
SELECT
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.barcode_sample AS barcode_sample,
h.sampletype AS reception_sampletype,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comment,
b.barcode_sample AS chemistry_barcode_sample,
i.sampletype AS chemistry_sampletype2bwat,
b.barcode_nitro AS chemistry_barcode_nitro,
b.3rdparty_lab AS chemistry_3rdparty_lab,
b.barcode_nitro2 AS chemistry_barcode_nitro_2nd,
b.3rdparty_lab2 AS chemistry_3rdparty_lab_2nd,
b.barcode_microbiology AS chemistry_barcode_microbiology,
b.3rdparty_lab3 AS chemistry_3rdparty_lab3,
b.barcode_microbiology2 AS chemistry_barcode_microbiology2,
b.3rdparty_lab4 AS chemistry_3rdparty_lab4,
b.comments AS chemistry_comment,
c.barcode_sample AS endetec_in_barcode_sample,
c.date_conduct AS endetec_in_date_conduct,
c.time_incubation AS endetec_in_time_incubation,
c.barcode_endetec AS endetec_in_barcode_endetec,
c.volume AS endetec_in_volume,
c.dilution AS endetec_in_dilution,
c.comments AS endetec_in_comment,
d.date_conduct AS endetec_out_date_conduct,
d.barcode_endetec AS endetec_out_barcode_endetec,
d.time_ecoli AS endetec_out_time_ecoli,
d.volume_ecoli AS endetec_out_volume_ecoli,
d.ecoli_cfu AS endetec_out_ecoli_cfu,
d.total_coliforms AS endetec_out_total_coliforms,
d.total_coli_cfu AS endetec_out_total_coli_cfu,
d.comments AS endetec_out_comments,
e.barcode_sample AS idexx_in_barcode_sample,
e.date_conduct AS idexx_in_date_conduct,
e.time_incubation AS idexx_in_time_incubation,
e.barcode_colilert AS idexx_in_barcode_colilert,
e.volume AS idexx_in_volume,
e.dilution AS idexx_in_dilution,
e.comments AS idexx_in_comments,
e.barcode_colilert2 AS idexx_in_barcode_colilert2,
e.volume2 AS idexx_in_volume2,
e.dilution2 AS idexx_in_dilution2,
e.comments2 AS idexx_in_comments2,
f.date_conduct AS idexx_out_date_conduct,
f.barcode_colilert AS idexx_out_barcode_colilert,
f.timeout_incubation AS idexx_out_timeout_incubation,
f.time_minutes AS idexx_out_time_minutes,
f.ecoli_largewells AS idexx_out_ecoli_largewells,
f.ecoli_smallwells AS idexx_out_ecoli_smallwells,
f.ecoli_mpn AS idexx_out_ecoli_mpn,
f.coliforms_largewells AS idexx_out_coliforms_largewells,
f.coliforms_smallwells AS idexx_out_coliforms_smallwells,
f.coliforms_mpn AS idexx_out_coliforms_mpn,
f.comments AS idexx_out_comments,
m.date_conduct AS idexx_out_date_conduct2,
m.barcode_colilert AS idexx_out_barcode_colilert2,
m.timeout_incubation AS idexx_out_timeout_incubation2,
m.time_minutes AS idexx_out_time_minutes2,
m.ecoli_largewells AS idexx_out_ecoli_largewells2,
m.ecoli_smallwells AS idexx_out_ecoli_smallwells2,
m.ecoli_mpn AS idexx_out_ecoli_mpn2,
m.coliforms_largewells AS idexx_out_coliforms_largewells2,
m.coliforms_smallwells AS idexx_out_coliforms_smallwells2,
m.coliforms_mpn AS idexx_out_coliforms_mpn2,
m.comments AS idexx_out_comments2,
g.date_conduct AS metagenomics_date_conduct,
g.barcode_sample AS metagenomics_barcode_sample,
g.volume_filtered AS metagenomics_volume_filtered,
g.time_started AS metagenomics_time_started,
g.time_finished AS metagenomics_time_finished,
g.time_minutes AS metagenomics_time_minutes,
g.barcode_dna_bag AS metagenomics_barcode_dna_bag,
g.barcode_storage AS metagenomics_barcode_storage,
concat("F",j.freezer,"-","S",j.shelf,"-","R",j.rack,"-","DRW",j.rack_level) AS metagenomics_location,
g.comments AS metagenomics_comments,
k.bar_macconkey AS mac1_barcode_macconkey,
k.date_process AS mac1_date_process,
k.time_process AS mac1_time_process,
k.volume AS mac1_volume,
k.comments AS mac1_comments,
l.date_process AS mac2_date_process,
l.time_process AS mac2_time_process,
l.bar_macsweep1 AS mac2_bar_macsweep1,
l.cryobox1 AS mac2_cryobox1,
concat("F",n.freezer,"-","S",n.shelf,"-","R",n.rack,"-","DRW",n.rack_level) AS mac2_location1,
l.bar_macsweep2 AS mac2_bar_macsweep2,
l.cryobox2 AS mac2_cryobox2,
concat("F",o.freezer,"-","S",o.shelf,"-","R",o.rack,"-","DRW",o.rack_level) AS mac2_location2,
l.comments AS mac2_comments
FROM obj2b_receipt a
LEFT JOIN obj2b_chemistry b ON a.barcode_sample=b.barcode_sample AND b.flag = 0 AND b.lab = {lab}
LEFT JOIN obj2b_endetec1 c ON a.barcode_sample=c.barcode_sample
LEFT JOIN obj2b_endetec2 d ON c.barcode_endetec=d.barcode_endetec
LEFT JOIN obj2b_idexx1 e ON a.barcode_sample=e.barcode_sample
LEFT JOIN obj2b_idexx2 f ON e.barcode_colilert=f.barcode_colilert
LEFT JOIN obj2b_idexx2 m ON e.barcode_colilert2=m.barcode_colilert
LEFT JOIN obj2b_metagenomics g ON a.barcode_sample=g.barcode_sample
LEFT JOIN ref_sampletype h ON a.id_type2b=h.id_sampletype
LEFT JOIN ref_sampletype i ON b.id_type2bwat=i.id_sampletype
LEFT JOIN ref_location_80 j ON g.id_location_80=j.id_location_80 AND j.lab = {lab}
LEFT JOIN obj2b_mac1 k ON k.barcode_sample=a.barcode_sample
LEFT JOIN obj2b_mac2 l ON l.bar_macconkey=k.bar_macconkey
LEFT JOIN ref_location_80 n ON l.id_location_80_1=n.id_location_80 AND n.lab = {lab}
LEFT JOIN ref_location_80 o ON l.id_location_80_2=o.id_location_80 AND o.lab = {lab}
WHERE a.id_type2b = 6{where_rep} AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['barcode_sample', 'barcode_sample'],
                    ['reception_sampletype', 'reception_sampletype'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comment', 'reception_comment'],
                    ['chemistry_barcode_sample', 'chemistry_barcode_sample'],
                    ['chemistry_sampletype2bwat', 'chemistry_sampletype2bwat'],
                    [
                        [
                            'lab1' => 'chems_BTKL_barcode',
                            '*' => 'chems_WAF_barcode',
                        ],
                        'chemistry_barcode_nitro',
                    ],
                    [
                        [
                            'lab1' => 'chems_BTKL_deliver',
                            '*' => 'chems_WAF_deliver',
                        ],
                        'chemistry_3rdparty_lab',
                    ],
                    [
                        [
                            'lab1' => 'chems_BBLK_barcode',
                            '*' => 'chems_Other_barcode',
                        ],
                        'chemistry_barcode_nitro_2nd',
                    ],
                    [
                        [
                            'lab1' => 'chems_BBLK_deliver',
                            '*' => 'chems_Other_deliver',
                        ],
                        'chemistry_3rdparty_lab_2nd',
                    ],
                    [
                        [
                            'lab1' => 'micro_BTKL_barcode',
                            '*' => 'micro_WAF_barcode',
                        ],
                        'chemistry_barcode_microbiology',
                    ],
                    [
                        [
                            'lab1' => 'micro_BTKL_deliver',
                            '*' => 'micro_WAF_deliver',
                        ],
                        'chemistry_3rdparty_lab3',
                    ],
                    [
                        [
                            'lab1' => 'micro_BBLK_barcode',
                            '*' => 'micro_Other_barcode',
                        ],
                        'chemistry_barcode_microbiology2',
                    ],
                    [
                        [
                            'lab1' => 'micro_BBLK_deliver',
                            '*' => 'micro_Other_deliver',
                        ],
                        'chemistry_3rdparty_lab4',
                    ],
                    ['chemistry_comment', 'chemistry_comment'],
                    ['endetec_in_barcode_sample', 'endetec_in_barcode_sample'],
                    ['endetec_in_date_conduct', 'endetec_in_date_conduct'],
                    ['endetec_in_time_incubation', 'endetec_in_time_incubation'],
                    ['endetec_in_barcode_endetec', 'endetec_in_barcode_endetec'],
                    ['endetec_in_volume', 'endetec_in_volume'],
                    ['endetec_in_dilution', 'endetec_in_dilution'],
                    ['endetec_in_comment', 'endetec_in_comment'],
                    ['endetec_out_date_conduct', 'endetec_out_date_conduct'],
                    ['endetec_out_barcode_endetec', 'endetec_out_barcode_endetec'],
                    ['endetec_out_time_ecoli', 'endetec_out_time_ecoli'],
                    ['endetec_out_volume_ecoli', 'endetec_out_volume_ecoli'],
                    ['endetec_out_ecoli_cfu', 'endetec_out_ecoli_cfu'],
                    ['endetec_out_total_coliforms', 'endetec_out_total_coliforms'],
                    ['endetec_out_total_coli_cfu', 'endetec_out_total_coli_cfu'],
                    ['endetec_out_comments', 'endetec_out_comments'],
                    ['idexx_in_barcode_sample', 'idexx_in_barcode_sample'],
                    ['idexx_in_date_conduct', 'idexx_in_date_conduct'],
                    ['idexx_in_time_incubation', 'idexx_in_time_incubation'],
                    ['idexx_in_barcode_colilert', 'idexx_in_barcode_colilert'],
                    ['idexx_in_volume', 'idexx_in_volume'],
                    ['idexx_in_dilution', 'idexx_in_dilution'],
                    ['idexx_in_comments', 'idexx_in_comments'],
                    ['idexx_in_barcode_colilert2', 'idexx_in_barcode_colilert2'],
                    ['idexx_in_volume2', 'idexx_in_volume2'],
                    ['idexx_in_dilution2', 'idexx_in_dilution2'],
                    ['idexx_in_comments2', 'idexx_in_comments2'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_barcode_colilert', 'idexx_out_barcode_colilert'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    [
                        [
                            'rep6x2' => 'idexx_out_faecal_coliforms_largewells',
                            '*' => 'idexx_out_coliforms_largewells',
                        ],
                        'idexx_out_coliforms_largewells',
                    ],
                    [
                        [
                            'rep6x2' => 'idexx_out_faecal_coliforms_smallwells',
                            '*' => 'idexx_out_coliforms_smallwells',
                        ],
                        'idexx_out_coliforms_smallwells',
                    ],
                    [
                        [
                            'rep6x2' => 'idexx_out_faecal_coliforms_mpn',
                            '*' => 'idexx_out_coliforms_mpn',
                        ],
                        'idexx_out_coliforms_mpn',
                    ],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['idexx_out_date_conduct2', 'idexx_out_date_conduct2'],
                    ['idexx_out_barcode_colilert2', 'idexx_out_barcode_colilert2'],
                    ['idexx_out_timeout_incubation2', 'idexx_out_timeout_incubation2'],
                    ['idexx_out_time_minutes2', 'idexx_out_time_minutes2'],
                    ['idexx_out_ecoli_largewells2', 'idexx_out_ecoli_largewells2'],
                    ['idexx_out_ecoli_smallwells2', 'idexx_out_ecoli_smallwells2'],
                    ['idexx_out_ecoli_mpn2', 'idexx_out_ecoli_mpn2'],
                    ['idexx_out_coliforms_largewells2', 'idexx_out_coliforms_largewells2'],
                    ['idexx_out_coliforms_smallwells2', 'idexx_out_coliforms_smallwells2'],
                    ['idexx_out_coliforms_mpn2', 'idexx_out_coliforms_mpn2'],
                    ['idexx_out_comments2', 'idexx_out_comments2'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_sample', 'metagenomics_barcode_sample'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['mac2_location1', 'mac2_location1'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['mac2_location2', 'mac2_location2'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'bootsock' => [
                'sql' => <<<'SQL'
SELECT
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.barcode_sample AS barcode_sample,
m.sampletype AS reception_sampletype,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comment,
b.date_weighed AS bs_before_date_weighed_micro,
b.barcode_bootsocks AS bs_before_barcode_bootsocks_micro,
b.bootsock_weight_dry AS bs_before_bootsock_weight_dry_micro,
b.comments AS bs_before_comment_micro,
u.date_weighed AS bs_before_date_weighed_moisture,
u.barcode_bootsocks AS bs_before_barcode_bootsocks_moisture,
u.bootsock_weight_dry AS bs_before_bootsock_weight_dry_moisture,
u.comments AS bs_before_comment_moisture,
c.date_weighed AS bs_after_date_weighed_micro,
c.barcode_bootsocks AS bs_after_barcode_bootsocks_micro,
c.bootsock_weight_wet AS bs_after_bootsock_weight_wet_micro,
c.comments AS bs_after_comment_micro,
v.date_weighed AS bs_after_date_weighed_moisture,
v.barcode_bootsocks AS bs_after_barcode_bootsocks_moisture,
v.bootsock_weight_wet AS bs_after_bootsock_weight_wet_moisture,
v.comments AS bs_after_comment_moisture,
d.date_moisture AS old_mois_initial_date_moisture,
d.barcode_bootsock AS old_mois_initial_barcode_bootsocks,
d.barcode_foil AS old_mois_initial_barcode_foil,
d.foil_weight AS old_mois_initial_foil_weight,
d.wet_weight AS old_mois_initial_wet_weight,
d.time_incubator AS old_mois_initial_time_incubator,
d.comments AS old_mois_initial_comments,
r.date_moisture AS mois_initial_date_moisture,
r.barcode_foil AS mois_initial_barcode_foil_tray,
r.foil_weight AS mois_initial_foil_tray_weight,
r.time_filter_start AS mois_initial_time_filter_start,
r.time_filter_finish AS mois_initial_time_filter_finish,
r.wet_weight AS mois_initial_wet_weight,
r.time_incubator AS mois_initial_time_incubator,
r.comments AS mois_initial_comments,
e.date_moisture AS mois24_date_moisture,
e.barcode_foil AS mois24_barcode_foil,
e.dry_weight24 AS mois24_dry_weight24,
e.comments AS mois24_comments,
f.date_moisture AS mois48_date_moisture,
f.barcode_foil AS mois48_barcode_foil,
f.dry_weight48 AS mois48_dry_weight48,
f.difference AS mois48_difference,
f.comments AS mois48_comments,
y.date_moisture AS mois72_date_moisture,
y.barcode_foil AS mois72_barcode_foil,
y.dry_weight72 AS mois72_dry_weight72,
y.comments AS mois72_comments,
g.date_conduct AS stomacher_date_conduct,
g.barcode_bootsock AS stomacher_barcode_bootsocks1,
g.elution_no AS stomacher_elution_number_Micro1,
g.elution AS stomacher_elution_Micro1,
g.barcode_falcon AS stomacher_barcode_falcon_Micro1,
g.volume_stomacher AS stomacher_volume_Micro1,
o.elution_no AS stomacher_elution_number_Micro2,
o.elution AS stomacher_elution_Micro2,
o.barcode_falcon AS stomacher_barcode_falcon_Micro2,
o.volume_stomacher AS stomacher_volume_Micro2,
s.barcode_bootsock AS stomacher_barcode_bootsocks2,
s.elution_no AS stomacher_elution_number_Moisture1,
s.elution AS stomacher_elution_Moisture1,
s.barcode_falcon AS stomacher_barcode_falcon_Moisture1,
s.volume_stomacher AS stomacher_volume_Moisture1,
t.elution_no AS stomacher_elution_number_Moisture2,
t.elution AS stomacher_elution_Moisture2,
t.barcode_falcon AS stomacher_barcode_falcon_Moisture2,
t.volume_stomacher AS stomacher_volume_Moisture2,
h.barcode_endetec AS old_stom_endet_barcode_endetec,
h.barcode_falcon1 AS old_stom_endet_barcode_falcon1,
h.volume_falcon1 AS old_stom_endet_volume_falcon1,
h.barcode_falcon2 AS old_stom_endet_barcode_falcon2,
h.volume_falcon2 AS old_stom_endet_volume_falcon2,
h.dilution AS old_stom_endet_dilution,
h.time_incubation AS old_stom_endet_time_incu_start,
h.comments AS old_stom_endet_comments,
j.barcode_colilert AS old_stom_idexx_barcode_colilert,
j.barcode_falcon1 AS old_stom_idexx_barcode_falcon1,
j.volume_falcon1 AS old_stom_idexx_volume_falcon1,
j.barcode_falcon2 AS old_stom_idexx_barcode_falcon2,
j.volume_falcon2 AS old_stom_idexx_volume_falcon2,
j.dilution AS old_stom_idexx_dilution,
j.time_incubation AS old_stom_idexx_time_incu_start,
j.comments AS old_stom_idexx_comments,
w.barcode_endetec AS stom_endet_barcode_endetec,
w.barcode_falcon1 AS stom_endet_barcode_falcon1,
w.volume_falcon1 AS stom_endet_volume_falcon1,
w.barcode_falcon2 AS stom_endet_barcode_falcon2,
w.volume_falcon2 AS stom_endet_volume_falcon2,
w.dilution AS stom_endet_dilution,
w.time_incubation AS stom_endet_time_incu_start,
w.comments AS stom_endet_comments,
x.barcode_colilert AS stom_idexx_barcode_colilert,
x.barcode_falcon1 AS stom_idexx_barcode_falcon1,
x.volume_falcon1 AS stom_idexx_volume_falcon1,
x.barcode_falcon2 AS stom_idexx_barcode_falcon2,
x.volume_falcon2 AS stom_idexx_volume_falcon2,
x.dilution AS stom_idexx_dilution,
x.time_incubation AS stom_idexx_time_incu_start,
x.comments AS stom_idexx_comments,
i.date_conduct AS endet_out_b_date_conduct,
i.barcode_endetec AS endet_out_b_barcode_endetec,
i.time_ecoli AS endet_out_b_time_ecoli,
i.volume_ecoli AS endet_out_b_volume_ecoli,
i.total_coliforms AS endet_out_b_total_coliforms,
i.comments AS endet_out_b_comments,
kk.date_conduct AS old_idexx_out_date_conduct,
kk.barcode_colilert AS old_idexx_out_barcode_colilert,
kk.timeout_incubation AS old_idexx_out_timeout_incubation,
kk.time_minutes AS old_idexx_out_time_minutes,
kk.ecoli_largewells AS old_idexx_out_ecoli_largewells,
kk.ecoli_smallwells AS old_idexx_out_ecoli_smallwells,
kk.ecoli_mpn AS old_idexx_out_ecoli_mpn,
kk.coliforms_largewells AS old_idexx_out_coliforms_largewells,
kk.coliforms_smallwells AS old_idexx_out_coliforms_smallwells,
kk.coliforms_mpn AS old_idexx_out_coliforms_mpn,
kk.comments AS old_idexx_out_comments,
k.date_conduct AS idexx_out_date_conduct,
k.barcode_colilert AS idexx_out_barcode_colilert,
k.timeout_incubation AS idexx_out_timeout_incubation,
k.time_minutes AS idexx_out_time_minutes,
k.ecoli_largewells AS idexx_out_ecoli_largewells,
k.ecoli_smallwells AS idexx_out_ecoli_smallwells,
k.ecoli_mpn AS idexx_out_ecoli_mpn,
k.coliforms_largewells AS idexx_out_coliforms_largewells,
k.coliforms_smallwells AS idexx_out_coliforms_smallwells,
k.coliforms_mpn AS idexx_out_coliforms_mpn,
k.comments AS idexx_out_comments,
l.date_conduct AS metagenomics_date_conduct,
l.barcode_sample AS metagenomics_barcode_falcon1,
l.barcode_falcon2 AS metagenomics_barcode_falcon2,
l.volume_filtered AS metagenomics_volume_filtered,
l.time_started AS metagenomics_time_started,
l.time_finished AS metagenomics_time_finished,
l.time_minutes AS metagenomics_time_minutes,
l.barcode_dna_bag AS metagenomics_barcode_dna_bag,
l.barcode_storage AS metagenomics_barcode_storage,
concat("F",n.freezer,"-","S",n.shelf,"-","R",n.rack,"-","DRW",n.rack_level) AS metagenomics_location,
l.comments AS metagenomics_comments,
p.bar_macconkey AS mac1_barcode_macconkey,
p.date_process AS mac1_date_process,
p.time_process AS mac1_time_process,
p.volume AS mac1_volume,
p.comments AS mac1_comments,
q.date_process AS mac2_date_process,
q.time_process AS mac2_time_process,
q.bar_macsweep1 AS mac2_bar_macsweep1,
q.cryobox1 AS mac2_cryobox1,
concat("F",fr1.freezer,"-","S",fr1.shelf,"-","R",fr1.rack,"-","DRW",fr1.rack_level) AS mac2_location1,
q.bar_macsweep2 AS mac2_bar_macsweep2,
q.cryobox2 AS mac2_cryobox2,
concat("F",fr2.freezer,"-","S",fr2.shelf,"-","R",fr2.rack,"-","DRW",fr2.rack_level) AS mac2_location2,
q.comments AS mac2_comments
FROM obj2b_receipt a
LEFT JOIN obj2b_bs_stomacher g ON a.barcode_sample=g.barcode_sample AND g.elution_no="Micro1"
LEFT JOIN obj2b_bs_stomacher o ON a.barcode_sample=o.barcode_sample AND o.elution_no="Micro2"
LEFT JOIN obj2b_bs_stomacher s ON a.barcode_sample=s.barcode_sample AND s.elution_no="Moisture1"
LEFT JOIN obj2b_bs_stomacher t ON a.barcode_sample=t.barcode_sample AND t.elution_no="Moisture2"
LEFT JOIN obj2b_moisture1 d ON a.barcode_sample=d.barcode_sample
LEFT JOIN obj2b_moisture1 r ON s.barcode_falcon=r.barcode_sample AND s.elution_no="Moisture1"
LEFT JOIN obj2b_moisture2 e ON (r.barcode_foil=e.barcode_foil OR d.barcode_foil=e.barcode_foil)
LEFT JOIN obj2b_moisture3 f ON (r.barcode_foil=f.barcode_foil OR d.barcode_foil=f.barcode_foil)
LEFT JOIN obj2b_moisture4 y ON (r.barcode_foil=y.barcode_foil OR d.barcode_foil=y.barcode_foil)
LEFT JOIN obj2b_bootsocks_before b ON b.barcode_bootsocks=g.barcode_bootsock AND g.elution_no="Micro1"
LEFT JOIN obj2b_bootsocks_after c ON c.barcode_bootsocks=g.barcode_bootsock AND g.elution_no="Micro1"
LEFT JOIN obj2b_bootsocks_before u ON u.barcode_bootsocks=s.barcode_bootsock AND s.elution_no="Moisture1"
LEFT JOIN obj2b_bootsocks_after v ON v.barcode_bootsocks=s.barcode_bootsock AND s.elution_no="Moisture1"
LEFT JOIN obj2b_subbs_endetec h ON a.barcode_sample=h.barcode_sample
LEFT JOIN obj2b_subbs_idexx j ON a.barcode_sample=j.barcode_sample
LEFT JOIN obj2b_subbs_endetec w ON g.barcode_bootsock=w.barcode_sample
LEFT JOIN obj2b_subbs_idexx x ON g.barcode_bootsock=x.barcode_sample
LEFT JOIN obj2b_endetec3 i ON w.barcode_endetec=i.barcode_endetec
LEFT JOIN obj2b_idexx2 k ON x.barcode_colilert=k.barcode_colilert
LEFT JOIN obj2b_idexx2 kk ON j.barcode_colilert=kk.barcode_colilert
LEFT JOIN obj2b_metagenomics l ON (l.barcode_sample=g.barcode_falcon OR l.barcode_sample=g.barcode_sample) AND g.elution_no="Micro1"
LEFT JOIN obj2b_mac1 p ON p.barcode_sample=g.barcode_falcon AND g.elution_no="Micro1"
LEFT JOIN obj2b_mac2 q ON q.bar_macconkey=p.bar_macconkey
LEFT JOIN ref_sampletype m ON a.id_type2b=m.id_sampletype
LEFT JOIN ref_location_80 n ON l.id_location_80=n.id_location_80 AND n.lab = {lab}
LEFT JOIN ref_location_80 fr1 ON q.id_location_80_1=fr1.id_location_80 AND fr1.lab = {lab}
LEFT JOIN ref_location_80 fr2 ON q.id_location_80_2=fr2.id_location_80 AND fr2.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['barcode_sample', 'barcode_sample'],
                    ['reception_sampletype', 'reception_sampletype'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comment', 'reception_comment'],
                    ['bs_before_date_weighed_micro', 'bs_before_date_weighed_micro'],
                    ['bs_before_barcode_bootsocks_micro', 'bs_before_barcode_bootsocks_micro'],
                    ['bs_before_bootsock_weight_dry_micro', 'bs_before_bootsock_weight_dry_micro'],
                    ['bs_before_comment_micro', 'bs_before_comment_micro'],
                    ['bs_before_date_weighed_moisture', 'bs_before_date_weighed_moisture'],
                    ['bs_before_barcode_bootsocks_moisture', 'bs_before_barcode_bootsocks_moisture'],
                    ['bs_before_bootsock_weight_dry_moisture', 'bs_before_bootsock_weight_dry_moisture'],
                    ['bs_before_comment_moisture', 'bs_before_comment_moisture'],
                    ['bs_after_date_weighed_micro', 'bs_after_date_weighed_micro'],
                    ['bs_after_barcode_bootsocks_micro', 'bs_after_barcode_bootsocks_micro'],
                    ['bs_after_bootsock_weight_wet_micro', 'bs_after_bootsock_weight_wet_micro'],
                    ['bs_after_comment_micro', 'bs_after_comment_micro'],
                    ['bs_after_date_weighed_moisture', 'bs_after_date_weighed_moisture'],
                    ['bs_after_barcode_bootsocks_moisture', 'bs_after_barcode_bootsocks_moisture'],
                    ['bs_after_bootsock_weight_wet_moisture', 'bs_after_bootsock_weight_wet_moisture'],
                    ['bs_after_comment_moisture', 'bs_after_comment_moisture'],
                    ['old_mois_initial_date_moisture', 'old_mois_initial_date_moisture'],
                    ['old_mois_initial_barcode_bootsocks', 'old_mois_initial_barcode_bootsocks'],
                    ['old_mois_initial_barcode_foil', 'old_mois_initial_barcode_foil'],
                    ['old_mois_initial_foil_weight', 'old_mois_initial_foil_weight'],
                    ['old_mois_initial_wet_weight', 'old_mois_initial_wet_weight'],
                    ['old_mois_initial_time_incubator', 'old_mois_initial_time_incubator'],
                    ['old_mois_initial_comments', 'old_mois_initial_comments'],
                    ['mois_initial_date_moisture', 'mois_initial_date_moisture'],
                    ['mois_initial_barcode_foil_tray', 'mois_initial_barcode_foil_tray'],
                    ['mois_initial_foil_tray_weight', 'mois_initial_foil_tray_weight'],
                    ['mois_initial_time_filter_start', 'mois_initial_time_filter_start'],
                    ['mois_initial_time_filter_finish', 'mois_initial_time_filter_finish'],
                    ['mois_initial_wet_weight', 'mois_initial_wet_weight'],
                    ['mois_initial_time_incubator', 'mois_initial_time_incubator'],
                    ['mois_initial_comments', 'mois_initial_comments'],
                    ['mois24_date_moisture', 'mois24_date_moisture'],
                    ['mois24_barcode_foil', 'mois24_barcode_foil'],
                    ['mois24_dry_weight24', 'mois24_dry_weight24'],
                    ['mois24_comments', 'mois24_comments'],
                    ['mois48_date_moisture', 'mois48_date_moisture'],
                    ['mois48_barcode_foil', 'mois48_barcode_foil'],
                    ['mois48_dry_weight48', 'mois48_dry_weight48'],
                    ['mois48_difference', 'mois48_difference'],
                    ['mois48_comments', 'mois48_comments'],
                    ['mois72_date_moisture', 'mois72_date_moisture'],
                    ['mois72_barcode_foil', 'mois72_barcode_foil'],
                    ['mois72_dry_weight72', 'mois72_dry_weight72'],
                    ['mois72_comments', 'mois72_comments'],
                    ['stomacher_date_conduct', 'stomacher_date_conduct'],
                    ['stomacher_barcode_bootsocks1', 'stomacher_barcode_bootsocks1'],
                    ['stomacher_elution_number_Micro1', 'stomacher_elution_number_Micro1'],
                    ['stomacher_elution_Micro1', 'stomacher_elution_Micro1'],
                    ['stomacher_barcode_falcon_Micro1', 'stomacher_barcode_falcon_Micro1'],
                    ['stomacher_volume_Micro1', 'stomacher_volume_Micro1'],
                    ['stomacher_elution_number_Micro2', 'stomacher_elution_number_Micro2'],
                    ['stomacher_elution_Micro2', 'stomacher_elution_Micro2'],
                    ['stomacher_barcode_falcon_Micro2', 'stomacher_barcode_falcon_Micro2'],
                    ['stomacher_volume_Micro2', 'stomacher_volume_Micro2'],
                    ['stomacher_barcode_bootsocks2', 'stomacher_barcode_bootsocks2'],
                    ['stomacher_elution_number_Moisture1', 'stomacher_elution_number_Moisture1'],
                    ['stomacher_elution_Moisture1', 'stomacher_elution_Moisture1'],
                    ['stomacher_barcode_falcon_Moisture1', 'stomacher_barcode_falcon_Moisture1'],
                    ['stomacher_volume_Moisture1', 'stomacher_volume_Moisture1'],
                    ['stomacher_elution_number_Moisture2', 'stomacher_elution_number_Moisture2'],
                    ['stomacher_elution_Moisture2', 'stomacher_elution_Moisture2'],
                    ['stomacher_barcode_falcon_Moisture2', 'stomacher_barcode_falcon_Moisture2'],
                    ['stomacher_volume_Moisture2', 'stomacher_volume_Moisture2'],
                    ['old_stom_endet_barcode_endetec', 'old_stom_endet_barcode_endetec'],
                    ['old_stom_endet_barcode_falcon1', 'old_stom_endet_barcode_falcon1'],
                    ['old_stom_endet_volume_falcon1', 'old_stom_endet_volume_falcon1'],
                    ['old_stom_endet_barcode_falcon2', 'old_stom_endet_barcode_falcon2'],
                    ['old_stom_endet_volume_falcon2', 'old_stom_endet_volume_falcon2'],
                    ['old_stom_endet_dilution', 'old_stom_endet_dilution'],
                    ['old_stom_endet_time_incu_start', 'old_stom_endet_time_incu_start'],
                    ['old_stom_endet_comments', 'old_stom_endet_comments'],
                    ['old_stom_idexx_barcode_colilert', 'old_stom_idexx_barcode_colilert'],
                    ['old_stom_idexx_barcode_falcon1', 'old_stom_idexx_barcode_falcon1'],
                    ['old_stom_idexx_volume_falcon1', 'old_stom_idexx_volume_falcon1'],
                    ['old_stom_idexx_barcode_falcon2', 'old_stom_idexx_barcode_falcon2'],
                    ['old_stom_idexx_volume_falcon2', 'old_stom_idexx_volume_falcon2'],
                    ['old_stom_idexx_dilution', 'old_stom_idexx_dilution'],
                    ['old_stom_idexx_time_incu_start', 'old_stom_idexx_time_incu_start'],
                    ['old_stom_idexx_comments', 'old_stom_idexx_comments'],
                    ['stom_endet_barcode_endetec', 'stom_endet_barcode_endetec'],
                    ['stom_endet_barcode_falcon1', 'stom_endet_barcode_falcon1'],
                    ['stom_endet_volume_falcon1', 'stom_endet_volume_falcon1'],
                    ['stom_endet_barcode_falcon2', 'stom_endet_barcode_falcon2'],
                    ['stom_endet_volume_falcon2', 'stom_endet_volume_falcon2'],
                    ['stom_endet_dilution', 'stom_endet_dilution'],
                    ['stom_endet_time_incu_start', 'stom_endet_time_incu_start'],
                    ['stom_endet_comments', 'stom_endet_comments'],
                    ['stom_idexx_barcode_colilert', 'stom_idexx_barcode_colilert'],
                    ['stom_idexx_barcode_falcon1', 'stom_idexx_barcode_falcon1'],
                    ['stom_idexx_volume_falcon1', 'stom_idexx_volume_falcon1'],
                    ['stom_idexx_barcode_falcon2', 'stom_idexx_barcode_falcon2'],
                    ['stom_idexx_volume_falcon2', 'stom_idexx_volume_falcon2'],
                    ['stom_idexx_dilution', 'stom_idexx_dilution'],
                    ['stom_idexx_time_incu_start', 'stom_idexx_time_incu_start'],
                    ['stom_idexx_comments', 'stom_idexx_comments'],
                    ['endet_out(b)_date_conduct', 'endet_out_b_date_conduct'],
                    ['endet_out(b)_barcode_endetec', 'endet_out_b_barcode_endetec'],
                    ['endet_out(b)_time_ecoli', 'endet_out_b_time_ecoli'],
                    ['endet_out(b)_volume_ecoli', 'endet_out_b_volume_ecoli'],
                    ['endet_out(b)_total_coliforms', 'endet_out_b_total_coliforms'],
                    ['endet_out(b)_comments', 'endet_out_b_comments'],
                    ['old_idexx_out_date_conduct', 'old_idexx_out_date_conduct'],
                    ['old_idexx_out_barcode_colilert', 'old_idexx_out_barcode_colilert'],
                    ['old_idexx_out_timeout_incubation', 'old_idexx_out_timeout_incubation'],
                    ['old_idexx_out_time_minutes', 'old_idexx_out_time_minutes'],
                    ['old_idexx_out_ecoli_largewells', 'old_idexx_out_ecoli_largewells'],
                    ['old_idexx_out_ecoli_smallwells', 'old_idexx_out_ecoli_smallwells'],
                    ['old_idexx_out_ecoli_mpn', 'old_idexx_out_ecoli_mpn'],
                    ['old_idexx_out_coliforms_largewells', 'old_idexx_out_coliforms_largewells'],
                    ['old_idexx_out_coliforms_smallwells', 'old_idexx_out_coliforms_smallwells'],
                    ['old_idexx_out_coliforms_mpn', 'old_idexx_out_coliforms_mpn'],
                    ['old_idexx_out_comments', 'old_idexx_out_comments'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_barcode_colilert', 'idexx_out_barcode_colilert'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_falcon1', 'metagenomics_barcode_falcon1'],
                    ['metagenomics_barcode_falcon2', 'metagenomics_barcode_falcon2'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['mac2_location1', 'mac2_location1'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['mac2_location2', 'mac2_location2'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'sediment' => [
                'sql' => <<<'SQL'
SELECT
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.barcode_sample AS barcode_sample,
k.sampletype AS reception_sampletype,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comment,
b.date_conduct AS sedi_prep_date_conduct,
b.barcode_sample AS sedi_prep_barcode_sample,
b.barcode_tube AS sedi_prep_barcode_tube,
b.subsample_wet AS sedi_prep_subsample_wet,
b.elution AS sedi_prep_elution,
c.date_moisture AS mois_initial_date_moisture,
c.barcode_sample AS mois_initial_barcode_sample,
c.barcode_foil AS mois_initial_barcode_foil,
c.foil_weight AS mois_initial_foil_weight,
c.wet_weight AS mois_initial_wet_weight,
c.time_incubator AS mois_initial_time_incubator,
c.comments AS mois_initial_comments,
d.date_moisture AS mois24_date_moisture,
d.barcode_foil AS mois24_barcode_foil,
d.dry_weight24 AS mois24_dry_weight24,
d.comments AS mois24_comments,
e.date_moisture AS mois48_date_moisture,
e.barcode_foil AS mois48_barcode_foil,
e.dry_weight48 AS mois48_dry_weight48,
e.difference AS mois48_difference,
e.comments AS mois48_comments,
s.date_moisture AS mois72_date_moisture,
s.barcode_foil AS mois72_barcode_foil,
s.dry_weight72 AS mois72_dry_weight72,
s.comments AS mois72_comments,
f.barcode_endetec AS sedi_endet_barcode_endetec,
f.volume_falcon AS sedi_endet_volume_falcon,
f.dilution AS sedi_endet_dilution,
f.time_incubation AS sedi_endet_time_incu_start,
f.volume_falcon AS sedi_endet_volume_falcon,
f.comments AS sedi_endet_comments,
g.date_conduct AS endet_out_s_date_conduct,
g.barcode_endetec AS endet_out_s_barcode_endetec,
g.time_ecoli AS endet_out_s_time_ecoli,
g.volume_ecoli AS endet_out_s_volume_ecoli,
g.total_coliforms AS endet_out_s_total_coliforms,
g.comments AS endet_out_s_comments,
h.barcode_colilert AS sedi_idexx_barcode_colilert,
h.volume AS sedi_idexx_volume,
h.dilution AS sedi_idexx_dilution,
h.time_incubation AS sedi_idexx_time_incubation,
h.comments AS sedi_idexx_comments,
q.barcode_colilert AS sedi_idexx_barcode_colilert2,
q.volume AS sedi_idexx_volume2,
q.dilution AS sedi_idexx_dilution2,
q.time_incubation AS sedi_idexx_time_incubation2,
q.comments AS sedi_idexx_comments2,
i.date_conduct AS idexx_out_date_conduct,
i.timeout_incubation AS idexx_out_timeout_incubation,
i.time_minutes AS idexx_out_time_minutes,
i.ecoli_largewells AS idexx_out_ecoli_largewells,
i.ecoli_smallwells AS idexx_out_ecoli_smallwells,
i.ecoli_mpn AS idexx_out_ecoli_mpn,
i.coliforms_largewells AS idexx_out_coliforms_largewells,
i.coliforms_smallwells AS idexx_out_coliforms_smallwells,
i.coliforms_mpn AS idexx_out_coliforms_mpn,
i.comments AS idexx_out_comments,
r.date_conduct AS idexx_out_date_conduct2,
r.timeout_incubation AS idexx_out_timeout_incubation2,
r.time_minutes AS idexx_out_time_minutes2,
r.ecoli_largewells AS idexx_out_ecoli_largewells2,
r.ecoli_smallwells AS idexx_out_ecoli_smallwells2,
r.ecoli_mpn AS idexx_out_ecoli_mpn2,
r.coliforms_largewells AS idexx_out_coliforms_largewells2,
r.coliforms_smallwells AS idexx_out_coliforms_smallwells2,
r.coliforms_mpn AS idexx_out_coliforms_mpn2,
r.comments AS idexx_out_comments2,
j.date_conduct AS metagenomics_date_conduct,
j.barcode_sample AS metagenomics_barcode_sample,
j.barcode_dna1 AS metagenomics_barcode_dna1,
j.weight_sub1 AS metagenomics_weight_sub1,
j.barcode_storage1 AS metagenomics_barcode_storage1,
j.position_tube1 AS metagenomics_position_tube1,
CONCAT("F",l.freezer,"-","S",l.shelf,"-","R",l.rack,"-","DRW",l.rack_level) AS metagenomics_location1,
j.barcode_dna2 AS metagenomics_barcode_dna2,
j.weight_sub2 AS metagenomics_weight_sub2,
j.barcode_storage2 AS metagenomics_barcode_storage2,
j.position_tube2 AS metagenomics_position_tube2,
CONCAT("F",m.freezer,"-","S",m.shelf,"-","R",m.rack,"-","DRW",m.rack_level) AS metagenomics_location2,
j.comments AS metagenomics_comments,
o.bar_macconkey AS mac1_barcode_macconkey,
o.date_process AS mac1_date_process,
o.time_process AS mac1_time_process,
o.volume AS mac1_volume,
o.comments AS mac1_comments,
p.date_process AS mac2_date_process,
p.time_process AS mac2_time_process,
p.bar_macsweep1 AS mac2_bar_macsweep1,
p.cryobox1 AS mac2_cryobox1,
concat("F",fr1.freezer,"-","S",fr1.shelf,"-","R",fr1.rack,"-","DRW",fr1.rack_level) AS mac2_location1,
p.bar_macsweep2 AS mac2_bar_macsweep2,
p.cryobox2 AS mac2_cryobox2,
concat("F",fr2.freezer,"-","S",fr2.shelf,"-","R",fr2.rack,"-","DRW",fr2.rack_level) AS mac2_location2,
p.comments AS mac2_comments
FROM obj2b_receipt a
LEFT JOIN obj2b_sediment_prep b ON a.barcode_sample=b.barcode_sample
LEFT JOIN obj2b_moisture1 c ON a.barcode_sample=c.barcode_sample
LEFT JOIN obj2b_moisture2 d ON c.barcode_foil=d.barcode_foil
LEFT JOIN obj2b_moisture3 e ON c.barcode_foil=e.barcode_foil
LEFT JOIN obj2b_moisture4 s ON c.barcode_foil=s.barcode_foil
LEFT JOIN obj2b_subsd_endetec f ON b.barcode_sample=f.barcode_sample
LEFT JOIN obj2b_endetec3 g ON f.barcode_endetec=g.barcode_endetec
LEFT JOIN obj2b_subsd_idexx h ON b.barcode_sample=h.barcode_sample
LEFT JOIN obj2b_subsd_idexx q ON b.barcode_sample=q.barcode_sample AND h.barcode_colilert <> q.barcode_colilert
LEFT JOIN obj2b_idexx2 i ON h.barcode_colilert=i.barcode_colilert
LEFT JOIN obj2b_idexx2 r ON q.barcode_colilert=r.barcode_colilert
LEFT JOIN obj2b_meta_sediment j ON a.barcode_sample=j.barcode_sample
LEFT JOIN ref_sampletype k ON a.id_type2b=k.id_sampletype
LEFT JOIN ref_location_80 l ON j.id_location_801=l.id_location_80 AND l.lab = {lab}
LEFT JOIN ref_location_80 m ON j.id_location_802=m.id_location_80 AND m.lab = {lab}
LEFT JOIN obj2b_mac1 o ON b.barcode_tube=o.barcode_sample
LEFT JOIN obj2b_mac2 p ON p.bar_macconkey=o.bar_macconkey
LEFT JOIN ref_location_80 fr1 ON p.id_location_80_1=fr1.id_location_80 AND fr1.lab = {lab}
LEFT JOIN ref_location_80 fr2 ON p.id_location_80_2=fr2.id_location_80 AND fr2.lab = {lab}

WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['barcode_sample', 'barcode_sample'],
                    ['reception_sampletype', 'reception_sampletype'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comment', 'reception_comment'],
                    ['sedi_prep_date_conduct', 'sedi_prep_date_conduct'],
                    ['sedi_prep_barcode_sample', 'sedi_prep_barcode_sample'],
                    ['sedi_prep_barcode_tube', 'sedi_prep_barcode_tube'],
                    ['sedi_prep_subsample_wet', 'sedi_prep_subsample_wet'],
                    ['sedi_prep_elution', 'sedi_prep_elution'],
                    ['mois_initial_date_moisture', 'mois_initial_date_moisture'],
                    ['mois_initial_barcode_sample', 'mois_initial_barcode_sample'],
                    ['mois_initial_barcode_foil', 'mois_initial_barcode_foil'],
                    ['mois_initial_foil_weight', 'mois_initial_foil_weight'],
                    ['mois_initial_wet_weight', 'mois_initial_wet_weight'],
                    ['mois_initial_time_incubator', 'mois_initial_time_incubator'],
                    ['mois_initial_comments', 'mois_initial_comments'],
                    ['mois24_date_moisture', 'mois24_date_moisture'],
                    ['mois24_barcode_foil', 'mois24_barcode_foil'],
                    ['mois24_dry_weight24', 'mois24_dry_weight24'],
                    ['mois24_comments', 'mois24_comments'],
                    ['mois48_date_moisture', 'mois48_date_moisture'],
                    ['mois48_barcode_foil', 'mois48_barcode_foil'],
                    ['mois48_dry_weight48', 'mois48_dry_weight48'],
                    ['mois48_difference', 'mois48_difference'],
                    ['mois48_comments', 'mois48_comments'],
                    ['mois72_date_moisture', 'mois72_date_moisture'],
                    ['mois72_barcode_foil', 'mois72_barcode_foil'],
                    ['mois72_dry_weight72', 'mois72_dry_weight72'],
                    ['mois72_comments', 'mois72_comments'],
                    ['sedi_endet_barcode_endetec', 'sedi_endet_barcode_endetec'],
                    ['sedi_endet_volume_falcon', 'sedi_endet_volume_falcon'],
                    ['sedi_endet_dilution', 'sedi_endet_dilution'],
                    ['sedi_endet_time_incu_start', 'sedi_endet_time_incu_start'],
                    ['sedi_endet_comments', 'sedi_endet_comments'],
                    ['endet_out(s)_date_conduct', 'endet_out_s_date_conduct'],
                    ['endet_out(s)_barcode_endetec', 'endet_out_s_barcode_endetec'],
                    ['endet_out(s)_time_ecoli', 'endet_out_s_time_ecoli'],
                    ['endet_out(s)_volume_ecoli', 'endet_out_s_volume_ecoli'],
                    ['endet_out(s)_total_coliforms', 'endet_out_s_total_coliforms'],
                    ['endet_out(s)_comments', 'endet_out_s_comments'],
                    ['sedi_idexx_barcode_colilert', 'sedi_idexx_barcode_colilert'],
                    ['sedi_idexx_volume', 'sedi_idexx_volume'],
                    ['sedi_idexx_dilution', 'sedi_idexx_dilution'],
                    ['sedi_idexx_time_incubation', 'sedi_idexx_time_incubation'],
                    ['sedi_idexx_comments', 'sedi_idexx_comments'],
                    ['sedi_idexx_barcode_colilert2', 'sedi_idexx_barcode_colilert2'],
                    ['sedi_idexx_volume2', 'sedi_idexx_volume2'],
                    ['sedi_idexx_dilution2', 'sedi_idexx_dilution2'],
                    ['sedi_idexx_time_incubation2', 'sedi_idexx_time_incubation2'],
                    ['sedi_idexx_comments2', 'sedi_idexx_comments2'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['idexx_out_date_conduct2', 'idexx_out_date_conduct2'],
                    ['idexx_out_timeout_incubation2', 'idexx_out_timeout_incubation2'],
                    ['idexx_out_time_minutes2', 'idexx_out_time_minutes2'],
                    ['idexx_out_ecoli_largewells2', 'idexx_out_ecoli_largewells2'],
                    ['idexx_out_ecoli_smallwells2', 'idexx_out_ecoli_smallwells2'],
                    ['idexx_out_ecoli_mpn2', 'idexx_out_ecoli_mpn2'],
                    ['idexx_out_coliforms_largewells2', 'idexx_out_coliforms_largewells2'],
                    ['idexx_out_coliforms_smallwells2', 'idexx_out_coliforms_smallwells2'],
                    ['idexx_out_coliforms_mpn2', 'idexx_out_coliforms_mpn2'],
                    ['idexx_out_comments2', 'idexx_out_comments2'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_sample', 'metagenomics_barcode_sample'],
                    ['metagenomics_barcode_dna1', 'metagenomics_barcode_dna1'],
                    ['metagenomics_weight_sub1', 'metagenomics_weight_sub1'],
                    ['metagenomics_barcode_storage1', 'metagenomics_barcode_storage1'],
                    ['metagenomics_position_tube1', 'metagenomics_position_tube1'],
                    ['metagenomics_location1', 'metagenomics_location1'],
                    ['metagenomics_barcode_dna2', 'metagenomics_barcode_dna2'],
                    ['metagenomics_weight_sub2', 'metagenomics_weight_sub2'],
                    ['metagenomics_barcode_storage2', 'metagenomics_barcode_storage2'],
                    ['metagenomics_position_tube2', 'metagenomics_position_tube2'],
                    ['metagenomics_location2', 'metagenomics_location2'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['mac2_location1', 'mac2_location1'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['mac2_location2', 'mac2_location2'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'feces' => [
                'sql' => <<<'SQL'
SELECT
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.barcode_sample AS barcode_sample,
k.sampletype AS reception_sampletype,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comment,
j.date_conduct AS metagenomics_date_conduct,
j.barcode_sample AS metagenomics_barcode_sample,
j.barcode_dna1 AS metagenomics_barcode_dna1,
j.weight_sub1 AS metagenomics_weight_sub1,
j.barcode_storage1 AS metagenomics_barcode_storage1,
j.position_tube1 AS metagenomics_position_tube1,
concat("F",l.freezer,"-","S",l.shelf,"-","R",l.rack,"-","DRW",l.rack_level) AS metagenomics_location1,
j.barcode_dna2 AS metagenomics_barcode_dna2,
j.weight_sub2 AS metagenomics_weight_sub2,
j.barcode_storage2 AS metagenomics_barcode_storage2,
j.position_tube2 AS metagenomics_position_tube2,
concat("F",m.freezer,"-","S",m.shelf,"-","R",m.rack,"-","DRW",m.rack_level) AS metagenomics_location2,
j.comments AS metagenomics_comments,
o.bar_macconkey AS mac1_barcode_macconkey,
o.date_process AS mac1_date_process,
o.time_process AS mac1_time_process,
o.volume AS mac1_volume,
o.comments AS mac1_comments,
p.date_process AS mac2_date_process,
p.time_process AS mac2_time_process,
p.bar_macsweep1 AS mac2_bar_macsweep1,
p.cryobox1 AS mac2_cryobox1,
concat("F",fr1.freezer,"-","S",fr1.shelf,"-","R",fr1.rack,"-","DRW",fr1.rack_level) AS mac2_location1,
p.bar_macsweep2 AS mac2_bar_macsweep2,
p.cryobox2 AS mac2_cryobox2,
concat("F",fr2.freezer,"-","S",fr2.shelf,"-","R",fr2.rack,"-","DRW",fr2.rack_level) AS mac2_location2,
p.comments AS mac2_comments
FROM obj2b_receipt a
LEFT JOIN obj2b_meta_sediment j ON a.barcode_sample=j.barcode_sample
LEFT JOIN obj2b_mac1 o ON j.barcode_sample=o.barcode_sample
LEFT JOIN obj2b_mac2 p ON p.bar_macconkey=o.bar_macconkey
LEFT JOIN ref_sampletype k ON a.id_type2b=k.id_sampletype
LEFT JOIN ref_location_80 l ON j.id_location_801=l.id_location_80 AND l.lab = {lab}
LEFT JOIN ref_location_80 m ON j.id_location_802=m.id_location_80 AND m.lab = {lab}
LEFT JOIN ref_location_80 fr1 ON p.id_location_80_1=fr1.id_location_80 AND fr1.lab = {lab}
LEFT JOIN ref_location_80 fr2 ON p.id_location_80_2=fr2.id_location_80 AND fr2.lab = {lab}

WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['barcode_sample', 'barcode_sample'],
                    ['reception_sampletype', 'reception_sampletype'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comment', 'reception_comment'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_sample', 'metagenomics_barcode_sample'],
                    ['metagenomics_barcode_dna1', 'metagenomics_barcode_dna1'],
                    ['metagenomics_weight_sub1', 'metagenomics_weight_sub1'],
                    ['metagenomics_barcode_storage1', 'metagenomics_barcode_storage1'],
                    ['metagenomics_position_tube1', 'metagenomics_position_tube1'],
                    ['metagenomics_location1', 'metagenomics_location1'],
                    ['metagenomics_barcode_dna2', 'metagenomics_barcode_dna2'],
                    ['metagenomics_weight_sub2', 'metagenomics_weight_sub2'],
                    ['metagenomics_barcode_storage2', 'metagenomics_barcode_storage2'],
                    ['metagenomics_position_tube2', 'metagenomics_position_tube2'],
                    ['metagenomics_location2', 'metagenomics_location2'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['mac2_location1', 'mac2_location1'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['mac2_location2', 'mac2_location2'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
        ],
        'preview' => [
            'columns' => [
                'barcode_sample' => 'Barcode sample',
                'date_arrival' => 'Date arrive',
                'time_arrival' => 'Time arrive',
                'sampletype' => 'Sample type',
                'png_control' => 'P&G control',
                'barcode_tinytag' => 'Barcode tinytag',
            ],
            'sql' => <<<'SQL'
SELECT a.barcode_sample, a.date_arrival, a.time_arrival, b.sampletype, a.png_control, a.barcode_tinytag
FROM obj2b_receipt a
LEFT JOIN ref_sampletype b ON a.id_type2b = b.id_sampletype
WHERE {type_where}
  AND a.lab = {lab} AND a.flag = 0
  AND a.date_arrival >= {d1} AND a.date_arrival <= {d2}
ORDER BY a.date_arrival DESC, a.time_arrival DESC, a.barcode_sample ASC
LIMIT 20000
SQL,
        ],
    ],

    // ============================================================
    'rep_nhmrc' => [
        'title' => 'REPORTS - NHMRC REPORT',
        'buttons' => [
            [
                'label' => 'Reception',
                'module' => 'nhmrc_sample_reception',
            ],
            [
                'label' => 'B.Before',
                'module' => 'nhmrc_bootsocks_before',
            ],
            [
                'label' => 'B.After',
                'module' => 'nhmrc_bootsocks_after',
            ],
            [
                'label' => 'Stomacher(B/T)',
                'module' => 'nhmrc_bootsocks_stomacher',
            ],
            [
                'label' => 'Stomacher (Food)',
                'module' => 'nhmrc_sample_prep',
            ],
            [
                'label' => 'Blank IN',
                'module' => 'nhmrc_blank_in',
            ],
            [
                'label' => 'IDEXX IN (HR)',
                'module' => 'nhmrc_idexx_in',
            ],
            [
                'label' => 'IDEXX Out(All)',
                'module' => 'nhmrc_idexx_out',
            ],
            [
                'label' => 'Mac In',
                'module' => 'nhmrc_macconkey_in',
            ],
            [
                'label' => 'Mac Out',
                'module' => 'nhmrc_macconkey_out',
            ],
            [
                'label' => 'Meta (B/R)',
                'module' => 'nhmrc_metagenomics_br',
            ],
            [
                'label' => 'Meta (Food)',
                'module' => 'nhmrc_metagenomics_food',
            ],
            [
                'label' => 'Mois (I)',
                'module' => 'nhmrc_moisture_initial',
            ],
            [
                'label' => 'Mois (24)',
                'module' => 'nhmrc_moisture_24',
            ],
            [
                'label' => 'Mois (48)',
                'module' => 'nhmrc_moisture_48',
            ],
            [
                'label' => 'Mois (72)',
                'module' => 'nhmrc_moisture_72',
            ],
            [
                'label' => 'Sample entry (HR)',
                'module' => 'nhmrc_sample_entry',
            ],
        ],
        'type_label' => 'Report for sample type',
        'types' => [
            '18' => [
                'label' => 'Porch Bootsocks',
                'query' => 'porchbootsock',
                'filename' => 'NHMRC_Porch_Bootsocks__report',
                'preview_where' => 'a.id_type2b = 18',
            ],
            '19' => [
                'label' => 'Indoor Bootsocks',
                'query' => 'porchbootsock',
                'filename' => 'NHMRC_Indoor_Bootsocks__report',
                'preview_where' => 'a.id_type2b = 19',
            ],
            '20' => [
                'label' => 'Outdoor Bootsocks',
                'query' => 'porchbootsock',
                'filename' => 'NHMRC_Outdoor_Bootsocks__report',
                'preview_where' => 'a.id_type2b = 20',
            ],
            '21' => [
                'label' => 'Hand Bootsocks',
                'query' => 'handboot',
                'filename' => 'NHMRC_Hand_Bootsocks_report',
                'preview_where' => 'a.id_type2b = 21',
            ],
            '22' => [
                'label' => 'Hand Rinse',
                'query' => 'handrince',
                'filename' => 'NHMRC_Hand_Rinse_report',
                'preview_where' => 'a.id_type2b = 22',
            ],
            '23' => [
                'label' => 'Food',
                'query' => 'food',
                'filename' => 'NHMRC_Food_report',
                'preview_where' => 'a.id_type2b = 23',
            ],
            '24' => [
                'label' => 'Hand Print',
                'query' => 'handprint',
                'filename' => 'NHMRC_Hand_Print',
                'preview_where' => 'a.id_type2b = 24',
            ],
            '25' => [
                'label' => 'Toys',
                'query' => 'toys',
                'filename' => 'NHMRC_Toys',
                'preview_where' => 'a.id_type2b = 25',
            ],
            '26' => [
                'label' => 'Swab Indoor',
                'query' => 'swab',
                'filename' => 'NHMRC_Swab_Indoor_report',
                'preview_where' => 'a.id_type2b = 26',
            ],
            '27' => [
                'label' => 'Drinking Water',
                'query' => 'drinkwater',
                'filename' => 'NHMRC_Drinking_Water_report',
                'preview_where' => 'a.id_type2b = 27',
            ],
        ],
        'queries' => [
            'porchbootsock' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, q.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
g.date_weighed AS bs_before_date_weighed_micro,
g.barcode_bootsocks AS bs_before_barcode_bootsocks_micro,
g.bootsock_weight_dry AS bs_before_bootsock_weight_dry_micro,
g.comments AS bs_before_comment_micro,
r.date_weighed AS bs_before_date_weighed_moisture,
r.barcode_bootsocks AS bs_before_barcode_bootsocks_moisture,
r.bootsock_weight_dry AS bs_before_bootsock_weight_dry_moisture,
r.comments AS bs_before_comment_moisture,
h.date_weighed AS bs_after_date_weighed_micro,
h.barcode_bootsocks AS bs_after_barcode_bootsocks_micro,
h.bootsock_weight_wet AS bs_after_bootsock_weight_wet_micro,
h.comments AS bs_after_comment_micro,
s.date_weighed AS bs_after_date_weighed_moisture,
s.barcode_bootsocks AS bs_after_barcode_bootsocks_moisture,
s.bootsock_weight_wet AS bs_after_bootsock_weight_wet_moisture,
s.comments AS bs_after_comment_moisture,
b.date_conduct AS stomacher_date_conduct,
b.barcode_bootsock AS stomacher_barcode_bootsocks_Micro,
b.elution_no AS stomacher_elution_number_Micro1,
b.elution AS stomacher_elution_Micro1,
b.barcode_falcon AS stomacher_barcode_falcon_Micro1,
b.volume_stomacher AS stomacher_volume_Micro1,
c.elution_no AS stomacher_elution_number_Micro2,
c.elution AS stomacher_elution_Micro2,
c.barcode_falcon AS stomacher_barcode_falcon_Micro2,
c.volume_stomacher AS stomacher_volume_Micro2,
d.barcode_bootsock AS stomacher_barcode_bootsocks_Moisture,
d.elution_no AS stomacher_elution_number_Moisture1,
d.elution AS stomacher_elution_Moisture1,
d.barcode_falcon AS stomacher_barcode_falcon_Moisture1,
d.volume_stomacher AS stomacher_volume_Moisture1,
e.elution_no AS stomacher_elution_number_Moisture2,
e.elution AS stomacher_elution_Moisture2,
e.barcode_falcon AS stomacher_barcode_falcon_Moisture2,
e.volume_stomacher AS stomacher_volume_Moisture2,
f.barcode_colilert AS stom_idexx_in_barcode_colilert,
f.barcode_falcon1 AS stom_idexx_in_barcode_falcon1,
f.volume_falcon1 AS stom_idexx_in_volume_falcon1,
f.barcode_falcon2 AS stom_idexx_in_barcode_falcon2,
f.volume_falcon2 AS stom_idexx_in_volume_falcon2,
f.dilution AS stom_idexx_in_dilution,
f.time_incubation AS stom_idexx_in_time_incu_start,
f.comments AS stom_idexx_in_comments,
j.date_conduct AS idexx_out_date_conduct,
j.timeout_incubation AS idexx_out_timeout_incubation,
j.time_minutes AS idexx_out_time_minutes,
j.ecoli_largewells AS idexx_out_ecoli_largewells,
j.ecoli_smallwells AS idexx_out_ecoli_smallwells,
j.ecoli_mpn AS idexx_out_ecoli_mpn,
j.coliforms_largewells AS idexx_out_coliforms_largewells,
j.coliforms_smallwells AS idexx_out_coliforms_smallwells,
j.coliforms_mpn AS idexx_out_coliforms_mpn,
j.comments AS idexx_out_comments,
k.date_moisture AS mois_initial_date_moisture,
k.barcode_foil AS mois_initial_barcode_foil_tray,
k.foil_weight AS mois_initial_foil_tray_weight,
k.time_filter_start AS mois_initial_time_filter_start,
k.time_filter_finish AS mois_initial_time_filter_finish,
k.wet_weight AS mois_initial_wet_weight,
k.time_incubator AS mois_initial_time_incubator,
k.comments AS mois_initial_comments,
l.date_moisture AS mois24_date_moisture,
l.dry_weight24 AS mois24_dry_weight24,
l.comments AS mois24_comments,
m.date_moisture AS mois48_date_moisture,
m.dry_weight48 AS mois48_dry_weight48,
m.difference AS mois48_difference,
m.comments AS mois48_comments,
n.date_moisture AS mois72_date_moisture,
n.dry_weight72 AS mois72_dry_weight48,
n.comments AS mois72_comments,
i.date_conduct AS metagenomics_date_conduct,
i.barcode_sample AS metagenomics_barcode_falcon1,
i.barcode_falcon2 AS metagenomics_barcode_falcon2,
i.volume_filtered AS metagenomics_volume_filtered,
i.time_started AS metagenomics_time_started,
i.time_finished AS metagenomics_time_finished,
i.time_minutes AS metagenomics_time_minutes,
i.barcode_dna_bag AS metagenomics_barcode_dna_bag,
i.barcode_storage AS metagenomics_barcode_storage,
concat("F",t.freezer,"-","S",t.shelf,"-","R",t.rack,"-","DRW",t.rack_level) AS metagenomics_location,
i.comments AS metagenomics_comments,
o.bar_macconkey AS mac1_barcode_macconkey,
o.date_process AS mac1_date_process,
o.time_process AS mac1_time_process,
o.volume AS mac1_volume,
o.comments AS mac1_comments,
p.date_process AS mac2_date_process,
p.time_process AS mac2_time_process,
p.bar_macsweep1 AS mac2_bar_macsweep1,
p.cryobox1 AS mac2_cryobox1,
concat("F",u.freezer,"-","S",u.shelf,"-","R",u.rack,"-","DRW",u.rack_level) AS macsweep1_location,
p.bar_macsweep2 AS mac2_bar_macsweep2,
p.cryobox2 AS mac2_cryobox2,
concat("F",v.freezer,"-","S",v.shelf,"-","R",v.rack,"-","DRW",v.rack_level) AS macsweep2_location,
p.comments AS mac2_comments

FROM nhmrc_receipt a
LEFT JOIN nhmrc_bs_stomacher b ON a.barcode_sample=b.barcode_sample AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bs_stomacher c ON a.barcode_sample=c.barcode_sample AND c.elution_no="Micro2"
LEFT JOIN nhmrc_bs_stomacher d ON a.barcode_sample=d.barcode_sample AND d.elution_no="Moisture1"
LEFT JOIN nhmrc_bs_stomacher e ON a.barcode_sample=e.barcode_sample AND e.elution_no="Moisture2"
LEFT JOIN nhmrc_subbs_idexx f ON b.barcode_bootsock=f.barcode_sample
LEFT JOIN nhmrc_bootsocks_before g ON b.barcode_bootsock=g.barcode_bootsocks AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bootsocks_after h ON b.barcode_bootsock=h.barcode_bootsocks AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bootsocks_before r ON d.barcode_bootsock=r.barcode_bootsocks AND d.elution_no="Moisture1"
LEFT JOIN nhmrc_bootsocks_after s ON d.barcode_bootsock=s.barcode_bootsocks AND d.elution_no="Moisture1"
LEFT JOIN nhmrc_metagenomics i ON f.barcode_falcon1=i.barcode_sample
LEFT JOIN nhmrc_idexx2 j ON f.barcode_colilert=j.barcode_colilert
LEFT JOIN nhmrc_moisture1 k ON d.barcode_falcon=k.barcode_sample
LEFT JOIN nhmrc_moisture2 l ON k.barcode_foil=l.barcode_foil
LEFT JOIN nhmrc_moisture3 m ON k.barcode_foil=m.barcode_foil
LEFT JOIN nhmrc_moisture4 n ON k.barcode_foil=n.barcode_foil
LEFT JOIN nhmrc_mac1 o ON f.barcode_falcon1=o.barcode_sample
LEFT JOIN nhmrc_mac2 p ON o.bar_macconkey=p.bar_macconkey
LEFT JOIN ref_sampletype q ON a.id_type2b = q.id_sampletype
LEFT JOIN ref_location_80 t ON i.id_location_80=t.id_location_80 AND t.lab = {lab}
LEFT JOIN ref_location_80 u ON p.id_location_80_1=u.id_location_80 AND u.lab = {lab}
LEFT JOIN ref_location_80 v ON p.id_location_80_2=v.id_location_80 AND v.lab = {lab}
        WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['bs_before_date_weighed_micro', 'bs_before_date_weighed_micro'],
                    ['bs_before_barcode_bootsocks_micro', 'bs_before_barcode_bootsocks_micro'],
                    ['bs_before_bootsock_weight_dry_micro', 'bs_before_bootsock_weight_dry_micro'],
                    ['bs_before_comment_micro', 'bs_before_comment_micro'],
                    ['bs_before_date_weighed_moisture', 'bs_before_date_weighed_moisture'],
                    ['bs_before_barcode_bootsocks_moisture', 'bs_before_barcode_bootsocks_moisture'],
                    ['bs_before_bootsock_weight_dry_moisture', 'bs_before_bootsock_weight_dry_moisture'],
                    ['bs_before_comment_moisture', 'bs_before_comment_moisture'],
                    ['bs_after_date_weighed_micro', 'bs_after_date_weighed_micro'],
                    ['bs_after_barcode_bootsocks_micro', 'bs_after_barcode_bootsocks_micro'],
                    ['bs_after_bootsock_weight_wet_micro', 'bs_after_bootsock_weight_wet_micro'],
                    ['bs_after_comment_micro', 'bs_after_comment_micro'],
                    ['bs_after_date_weighed_moisture', 'bs_after_date_weighed_moisture'],
                    ['bs_after_barcode_bootsocks_moisture', 'bs_after_barcode_bootsocks_moisture'],
                    ['bs_after_bootsock_weight_wet_moisture', 'bs_after_bootsock_weight_wet_moisture'],
                    ['bs_after_comment_moisture', 'bs_after_comment_moisture'],
                    ['stomacher_date_conduct', 'stomacher_date_conduct'],
                    ['stomacher_barcode_bootsocks_Micro', 'stomacher_barcode_bootsocks_Micro'],
                    ['stomacher_elution_number_Micro1', 'stomacher_elution_number_Micro1'],
                    ['stomacher_elution_Micro1', 'stomacher_elution_Micro1'],
                    ['stomacher_barcode_falcon_Micro1', 'stomacher_barcode_falcon_Micro1'],
                    ['stomacher_volume_Micro1', 'stomacher_volume_Micro1'],
                    ['stomacher_elution_number_Micro2', 'stomacher_elution_number_Micro2'],
                    ['stomacher_elution_Micro2', 'stomacher_elution_Micro2'],
                    ['stomacher_barcode_falcon_Micro2', 'stomacher_barcode_falcon_Micro2'],
                    ['stomacher_volume_Micro2', 'stomacher_volume_Micro2'],
                    ['stomacher_barcode_bootsocks_Moisture', 'stomacher_barcode_bootsocks_Moisture'],
                    ['stomacher_elution_number_Moisture1', 'stomacher_elution_number_Moisture1'],
                    ['stomacher_elution_Moisture1', 'stomacher_elution_Moisture1'],
                    ['stomacher_barcode_falcon_Moisture1', 'stomacher_barcode_falcon_Moisture1'],
                    ['stomacher_volume_Moisture1', 'stomacher_volume_Moisture1'],
                    ['stomacher_elution_number_Moisture2', 'stomacher_elution_number_Moisture2'],
                    ['stomacher_elution_Moisture2', 'stomacher_elution_Moisture2'],
                    ['stomacher_barcode_falcon_Moisture2', 'stomacher_barcode_falcon_Moisture2'],
                    ['stomacher_volume_Moisture2', 'stomacher_volume_Moisture2'],
                    ['stom_idexx_in_barcode_colilert', 'stom_idexx_in_barcode_colilert'],
                    ['stom_idexx_in_barcode_falcon1', 'stom_idexx_in_barcode_falcon1'],
                    ['stom_idexx_in_volume_falcon1', 'stom_idexx_in_volume_falcon1'],
                    ['stom_idexx_in_barcode_falcon2', 'stom_idexx_in_barcode_falcon2'],
                    ['stom_idexx_in_volume_falcon2', 'stom_idexx_in_volume_falcon2'],
                    ['stom_idexx_in_dilution', 'stom_idexx_in_dilution'],
                    ['stom_idexx_in_time_incu_start', 'stom_idexx_in_time_incu_start'],
                    ['stom_idexx_in_comments', 'stom_idexx_in_comments'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['mois_initial_date_moisture', 'mois_initial_date_moisture'],
                    ['mois_initial_barcode_foil_tray', 'mois_initial_barcode_foil_tray'],
                    ['mois_initial_foil_tray_weight', 'mois_initial_foil_tray_weight'],
                    ['mois_initial_time_filter_start', 'mois_initial_time_filter_start'],
                    ['mois_initial_time_filter_finish', 'mois_initial_time_filter_finish'],
                    ['mois_initial_wet_weight', 'mois_initial_wet_weight'],
                    ['mois_initial_time_incubator', 'mois_initial_time_incubator'],
                    ['mois_initial_comments', 'mois_initial_comments'],
                    ['mois24_date_moisture', 'mois24_date_moisture'],
                    ['mois24_dry_weight24', 'mois24_dry_weight24'],
                    ['mois24_comments', 'mois24_comments'],
                    ['mois48_date_moisture', 'mois48_date_moisture'],
                    ['mois48_dry_weight48', 'mois48_dry_weight48'],
                    ['mois48_difference', 'mois48_difference'],
                    ['mois48_comments', 'mois48_comments'],
                    ['mois72_date_moisture', 'mois72_date_moisture'],
                    ['mois72_dry_weight48', 'mois72_dry_weight48'],
                    ['mois72_comments', 'mois72_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_falcon1', 'metagenomics_barcode_falcon1'],
                    ['metagenomics_barcode_falcon2', 'metagenomics_barcode_falcon2'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'handboot' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, q.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
g.date_weighed AS bs_before_date_weighed_micro,
g.barcode_bootsocks AS bs_before_barcode_bootsocks_micro,
g.bootsock_weight_dry AS bs_before_bootsock_weight_dry_micro,
g.comments AS bs_before_comment_micro,
h.date_weighed AS bs_after_date_weighed_micro,
h.barcode_bootsocks AS bs_after_barcode_bootsocks_micro,
h.bootsock_weight_wet AS bs_after_bootsock_weight_wet_micro,
h.comments AS bs_after_comment_micro,
b.date_conduct AS stomacher_date_conduct,
b.barcode_bootsock AS stomacher_barcode_bootsocks_Micro,
b.elution_no AS stomacher_elution_number_Micro1,
b.elution AS stomacher_elution_Micro1,
b.barcode_falcon AS stomacher_barcode_falcon_Micro1,
b.volume_stomacher AS stomacher_volume_Micro1,
c.elution_no AS stomacher_elution_number_Micro2,
c.elution AS stomacher_elution_Micro2,
c.barcode_falcon AS stomacher_barcode_falcon_Micro2,
c.volume_stomacher AS stomacher_volume_Micro2,
f.barcode_colilert AS stom_idexx_in_barcode_colilert,
f.barcode_falcon1 AS stom_idexx_in_barcode_falcon1,
f.volume_falcon1 AS stom_idexx_in_volume_falcon1,
f.barcode_falcon2 AS stom_idexx_in_barcode_falcon2,
f.volume_falcon2 AS stom_idexx_in_volume_falcon2,
f.dilution AS stom_idexx_in_dilution,
f.time_incubation AS stom_idexx_in_time_incu_start,
f.comments AS stom_idexx_in_comments,
j.date_conduct AS idexx_out_date_conduct,
j.timeout_incubation AS idexx_out_timeout_incubation,
j.time_minutes AS idexx_out_time_minutes,
j.ecoli_largewells AS idexx_out_ecoli_largewells,
j.ecoli_smallwells AS idexx_out_ecoli_smallwells,
j.ecoli_mpn AS idexx_out_ecoli_mpn,
j.coliforms_largewells AS idexx_out_coliforms_largewells,
j.coliforms_smallwells AS idexx_out_coliforms_smallwells,
j.coliforms_mpn AS idexx_out_coliforms_mpn,
j.comments AS idexx_out_comments,
i.date_conduct AS metagenomics_date_conduct,
i.barcode_sample AS metagenomics_barcode_falcon1,
i.barcode_falcon2 AS metagenomics_barcode_falcon2,
i.volume_filtered AS metagenomics_volume_filtered,
i.time_started AS metagenomics_time_started,
i.time_finished AS metagenomics_time_finished,
i.time_minutes AS metagenomics_time_minutes,
i.barcode_dna_bag AS metagenomics_barcode_dna_bag,
i.barcode_storage AS metagenomics_barcode_storage,
concat("F",t.freezer,"-","S",t.shelf,"-","R",t.rack,"-","DRW",t.rack_level) AS metagenomics_location,
i.comments AS metagenomics_comments,
o.bar_macconkey AS mac1_barcode_macconkey,
o.date_process AS mac1_date_process,
o.time_process AS mac1_time_process,
o.volume AS mac1_volume,
o.comments AS mac1_comments,
p.date_process AS mac2_date_process,
p.time_process AS mac2_time_process,
p.bar_macsweep1 AS mac2_bar_macsweep1,
p.cryobox1 AS mac2_cryobox1,
concat("F",u.freezer,"-","S",u.shelf,"-","R",u.rack,"-","DRW",u.rack_level) AS macsweep1_location,
p.bar_macsweep2 AS mac2_bar_macsweep2,
p.cryobox2 AS mac2_cryobox2,
concat("F",v.freezer,"-","S",v.shelf,"-","R",v.rack,"-","DRW",v.rack_level) AS macsweep2_location,
p.comments AS mac2_comments

FROM nhmrc_receipt a
LEFT JOIN nhmrc_bs_stomacher b ON a.barcode_sample=b.barcode_sample AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bs_stomacher c ON a.barcode_sample=c.barcode_sample AND c.elution_no="Micro2"
LEFT JOIN nhmrc_subbs_idexx f ON b.barcode_bootsock=f.barcode_sample
LEFT JOIN nhmrc_bootsocks_before g ON b.barcode_bootsock=g.barcode_bootsocks AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bootsocks_after h ON b.barcode_bootsock=h.barcode_bootsocks AND b.elution_no="Micro1"
LEFT JOIN nhmrc_metagenomics i ON f.barcode_falcon1=i.barcode_sample
LEFT JOIN nhmrc_idexx2 j ON f.barcode_colilert=j.barcode_colilert
LEFT JOIN nhmrc_mac1 o ON f.barcode_falcon1=o.barcode_sample
LEFT JOIN nhmrc_mac2 p ON o.bar_macconkey=p.bar_macconkey
LEFT JOIN ref_sampletype q ON a.id_type2b = q.id_sampletype
LEFT JOIN ref_location_80 t ON i.id_location_80=t.id_location_80 AND t.lab = {lab}
LEFT JOIN ref_location_80 u ON p.id_location_80_1=u.id_location_80 AND u.lab = {lab}
LEFT JOIN ref_location_80 v ON p.id_location_80_2=v.id_location_80 AND v.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['bs_before_date_weighed_micro', 'bs_before_date_weighed_micro'],
                    ['bs_before_barcode_bootsocks_micro', 'bs_before_barcode_bootsocks_micro'],
                    ['bs_before_bootsock_weight_dry_micro', 'bs_before_bootsock_weight_dry_micro'],
                    ['bs_before_comment_micro', 'bs_before_comment_micro'],
                    ['bs_after_date_weighed_micro', 'bs_after_date_weighed_micro'],
                    ['bs_after_barcode_bootsocks_micro', 'bs_after_barcode_bootsocks_micro'],
                    ['bs_after_bootsock_weight_wet_micro', 'bs_after_bootsock_weight_wet_micro'],
                    ['bs_after_comment_micro', 'bs_after_comment_micro'],
                    ['stomacher_date_conduct', 'stomacher_date_conduct'],
                    ['stomacher_barcode_bootsocks_Micro', 'stomacher_barcode_bootsocks_Micro'],
                    ['stomacher_elution_number_Micro1', 'stomacher_elution_number_Micro1'],
                    ['stomacher_elution_Micro1', 'stomacher_elution_Micro1'],
                    ['stomacher_barcode_falcon_Micro1', 'stomacher_barcode_falcon_Micro1'],
                    ['stomacher_volume_Micro1', 'stomacher_volume_Micro1'],
                    ['stomacher_elution_number_Micro2', 'stomacher_elution_number_Micro2'],
                    ['stomacher_elution_Micro2', 'stomacher_elution_Micro2'],
                    ['stomacher_barcode_falcon_Micro2', 'stomacher_barcode_falcon_Micro2'],
                    ['stomacher_volume_Micro2', 'stomacher_volume_Micro2'],
                    ['stom_idexx_in_barcode_colilert', 'stom_idexx_in_barcode_colilert'],
                    ['stom_idexx_in_barcode_falcon1', 'stom_idexx_in_barcode_falcon1'],
                    ['stom_idexx_in_volume_falcon1', 'stom_idexx_in_volume_falcon1'],
                    ['stom_idexx_in_barcode_falcon2', 'stom_idexx_in_barcode_falcon2'],
                    ['stom_idexx_in_volume_falcon2', 'stom_idexx_in_volume_falcon2'],
                    ['stom_idexx_in_dilution', 'stom_idexx_in_dilution'],
                    ['stom_idexx_in_time_incu_start', 'stom_idexx_in_time_incu_start'],
                    ['stom_idexx_in_comments', 'stom_idexx_in_comments'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_falcon1', 'metagenomics_barcode_falcon1'],
                    ['metagenomics_barcode_falcon2', 'metagenomics_barcode_falcon2'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'handrince' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, g.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
k.barcode_tube AS sampleentry_barcode_tube,
k.date_conduct AS sampleentry_date_conduct,
k.vol_aliquot AS sampleentry_vol_aliquot,
k.barcode_box AS sampleentry_barcode_box,
k.position_tube AS sampleentry_position_tube,
concat("F",l.freezer,"-","S",l.shelf,"-","R",l.rack,"-","DRW",l.rack_level) AS sampleentry_location,
k.comments AS sampleentry_comments,
b.date_conduct AS idexx_in_date_conduct,
b.time_incubation AS idexx_in_time_incubation,
b.barcode_colilert AS idexx_in_barcode_colilert1,
b.volume AS idexx_in_volume1,
b.dilution AS idexx_in_dilution1,
b.comments AS idexx_in_comments1,
b.barcode_colilert2 AS idexx_in_barcode_colilert2,
b.volume2 AS idexx_in_volume2,
b.dilution2 AS idexx_in_dilution2,
b.comments2 AS idexx_in_comments2,
c.date_conduct AS idexx_out_date_conduct,
c.timeout_incubation AS idexx_out_timeout_incubation,
c.time_minutes AS idexx_out_time_minutes,
c.ecoli_largewells AS idexx_out_ecoli_largewells,
c.ecoli_smallwells AS idexx_out_ecoli_smallwells,
c.ecoli_mpn AS idexx_out_ecoli_mpn,
c.coliforms_largewells AS idexx_out_coliforms_largewells,
c.coliforms_smallwells AS idexx_out_coliforms_smallwells,
c.coliforms_mpn AS idexx_out_coliforms_mpn,
c.comments AS idexx_out_comments,
d.date_conduct AS metagenomics_date_conduct,
d.barcode_sample AS metagenomics_barcode_falcon1,
d.barcode_falcon2 AS metagenomics_barcode_falcon2,
d.volume_filtered AS metagenomics_volume_filtered,
d.time_started AS metagenomics_time_started,
d.time_finished AS metagenomics_time_finished,
d.time_minutes AS metagenomics_time_minutes,
d.barcode_dna_bag AS metagenomics_barcode_dna_bag,
d.barcode_storage AS metagenomics_barcode_storage,
concat("F",h.freezer,"-","S",h.shelf,"-","R",h.rack,"-","DRW",h.rack_level) AS metagenomics_location,
d.comments AS metagenomics_comments,
e.bar_macconkey AS mac1_barcode_macconkey,
e.date_process AS mac1_date_process,
e.time_process AS mac1_time_process,
e.volume AS mac1_volume,
e.comments AS mac1_comments,
f.date_process AS mac2_date_process,
f.time_process AS mac2_time_process,
f.bar_macsweep1 AS mac2_bar_macsweep1,
f.cryobox1 AS mac2_cryobox1,
concat("F",i.freezer,"-","S",i.shelf,"-","R",i.rack,"-","DRW",i.rack_level) AS macsweep1_location,
f.bar_macsweep2 AS mac2_bar_macsweep2,
f.cryobox2 AS mac2_cryobox2,
concat("F",j.freezer,"-","S",j.shelf,"-","R",j.rack,"-","DRW",j.rack_level) AS macsweep2_location,
f.comments AS mac2_comments
FROM nhmrc_receipt a
LEFT JOIN nhmrc_idexx1 b ON a.barcode_sample=b.barcode_sample
LEFT JOIN nhmrc_idexx2 c ON b.barcode_colilert=c.barcode_colilert
LEFT JOIN nhmrc_metagenomics d ON a.barcode_sample=d.barcode_sample
LEFT JOIN nhmrc_mac1 e ON a.barcode_sample=e.barcode_sample
LEFT JOIN nhmrc_mac2 f ON e.bar_macconkey=f.bar_macconkey
LEFT JOIN ref_sampletype g ON a.id_type2b = g.id_sampletype
LEFT JOIN ref_location_80 h ON d.id_location_80=h.id_location_80 AND h.lab = {lab}
LEFT JOIN ref_location_80 i ON f.id_location_80_1=i.id_location_80 AND i.lab = {lab}
LEFT JOIN ref_location_80 j ON f.id_location_80_2=j.id_location_80 AND j.lab = {lab}
LEFT JOIN nhmrc_sample_entry k ON a.barcode_sample = k.barcode_sample
LEFT JOIN ref_location_80 l ON k.id_location_80=l.id_location_80 AND l.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['sampleentry_barcode_tube', 'sampleentry_barcode_tube'],
                    ['sampleentry_date_conduct', 'sampleentry_date_conduct'],
                    ['sampleentry_vol_aliquot', 'sampleentry_vol_aliquot'],
                    ['sampleentry_barcode_box', 'sampleentry_barcode_box'],
                    ['sampleentry_position_tube', 'sampleentry_position_tube'],
                    ['sampleentry_location', 'sampleentry_location'],
                    ['sampleentry_comments', 'sampleentry_comments'],
                    ['idexx_in_date_conduct', 'idexx_in_date_conduct'],
                    ['idexx_in_time_incubation', 'idexx_in_time_incubation'],
                    ['idexx_in_barcode_colilert1', 'idexx_in_barcode_colilert1'],
                    ['idexx_in_volume1', 'idexx_in_volume1'],
                    ['idexx_in_dilution1', 'idexx_in_dilution1'],
                    ['idexx_in_comments1', 'idexx_in_comments1'],
                    ['idexx_in_barcode_colilert2', 'idexx_in_barcode_colilert2'],
                    ['idexx_in_volume2', 'idexx_in_volume2'],
                    ['idexx_in_dilution2', 'idexx_in_dilution2'],
                    ['idexx_in_comments2', 'idexx_in_comments2'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_falcon1', 'metagenomics_barcode_falcon1'],
                    ['metagenomics_barcode_falcon2', 'metagenomics_barcode_falcon2'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'food' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, q.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
b.date_conduct AS stomacher_date_conduct,
b.barcode_food AS stomacher_barcode_food,
b.elution_no AS stomacher_elution_number,
b.elution AS stomacher_elution,
b.food_weight AS stomacher_food_weight,
b.food_comments AS stomacher_food_comments,
c.barcode_colilert AS stom_idexx_in_barcode_colilert,
c.barcode_falcon1 AS stom_idexx_in_barcode_falcon1,
c.volume_falcon1 AS stom_idexx_in_volume_falcon1,
c.barcode_falcon2 AS stom_idexx_in_barcode_falcon2,
c.volume_falcon2 AS stom_idexx_in_volume_falcon2,
c.dilution AS stom_idexx_in_dilution,
c.time_incubation AS stom_idexx_in_time_incu_start,
c.comments AS stom_idexx_in_comments,
d.date_conduct AS idexx_out_date_conduct,
d.timeout_incubation AS idexx_out_timeout_incubation,
d.time_minutes AS idexx_out_time_minutes,
d.ecoli_largewells AS idexx_out_ecoli_largewells,
d.ecoli_smallwells AS idexx_out_ecoli_smallwells,
d.ecoli_mpn AS idexx_out_ecoli_mpn,
d.coliforms_largewells AS idexx_out_coliforms_largewells,
d.coliforms_smallwells AS idexx_out_coliforms_smallwells,
d.coliforms_mpn AS idexx_out_coliforms_mpn,
d.comments AS idexx_out_comments,
k.date_moisture AS mois_initial_date_moisture,
k.barcode_foil AS mois_initial_barcode_foil_tray,
k.foil_weight AS mois_initial_foil_tray_weight,
k.time_filter_start AS mois_initial_time_filter_start,
k.time_filter_finish AS mois_initial_time_filter_finish,
k.wet_weight AS mois_initial_wet_weight,
k.time_incubator AS mois_initial_time_incubator,
k.comments AS mois_initial_comments,
l.date_moisture AS mois24_date_moisture,
l.dry_weight24 AS mois24_dry_weight24,
l.comments AS mois24_comments,
m.date_moisture AS mois48_date_moisture,
m.dry_weight48 AS mois48_dry_weight48,
m.difference AS mois48_difference,
m.comments AS mois48_comments,
n.date_moisture AS mois72_date_moisture,
n.dry_weight72 AS mois72_dry_weight48,
n.comments AS mois72_comments,
e.date_conduct AS metagenomics_date_conduct,
e.barcode_dna1 AS metagenomics_barcode_dna1,
e.weight_sub1 AS metagenomics_weight_sub1,
e.barcode_storage1 AS metagenomics_barcode_storage1,
e.position_tube1 AS metagenomics_position_tube1,
concat("F",t.freezer,"-","S",t.shelf,"-","R",t.rack,"-","DRW",t.rack_level) AS metagenomics_dna1_location,
e.barcode_dna2 AS metagenomics_barcode_dna2,
e.weight_sub2 AS metagenomics_weight_sub2,
e.barcode_storage2 AS metagenomics_barcode_storage2,
e.position_tube2 AS metagenomics_position_tube2,
concat("F",w.freezer,"-","S",w.shelf,"-","R",w.rack,"-","DRW",w.rack_level) AS metagenomics_dna2_location,
e.comments AS metagenomics_comments,
o.bar_macconkey AS mac1_barcode_macconkey,
o.date_process AS mac1_date_process,
o.time_process AS mac1_time_process,
o.volume AS mac1_volume,
o.comments AS mac1_comments,
p.date_process AS mac2_date_process,
p.time_process AS mac2_time_process,
p.bar_macsweep1 AS mac2_bar_macsweep1,
p.cryobox1 AS mac2_cryobox1,
concat("F",u.freezer,"-","S",u.shelf,"-","R",u.rack,"-","DRW",u.rack_level) AS macsweep1_location,
p.bar_macsweep2 AS mac2_bar_macsweep2,
p.cryobox2 AS mac2_cryobox2,
concat("F",v.freezer,"-","S",v.shelf,"-","R",v.rack,"-","DRW",v.rack_level) AS macsweep2_location,
p.comments AS mac2_comments
FROM nhmrc_receipt a
LEFT JOIN nhmrc_sample_prep b ON a.barcode_sample=b.barcode_sample
LEFT JOIN nhmrc_subsd_idexx c ON b.barcode_food=c.barcode_food
LEFT JOIN nhmrc_idexx2 d ON c.barcode_colilert=d.barcode_colilert
LEFT JOIN nhmrc_meta_food e ON a.barcode_sample=e.barcode_sample
LEFT JOIN nhmrc_moisture1 k ON a.barcode_sample=k.barcode_sample
LEFT JOIN nhmrc_moisture2 l ON k.barcode_foil=l.barcode_foil
LEFT JOIN nhmrc_moisture3 m ON k.barcode_foil=m.barcode_foil
LEFT JOIN nhmrc_moisture4 n ON k.barcode_foil=n.barcode_foil
LEFT JOIN nhmrc_mac1 o ON c.barcode_falcon1=o.barcode_sample
LEFT JOIN nhmrc_mac2 p ON o.bar_macconkey=p.bar_macconkey
LEFT JOIN ref_sampletype q ON a.id_type2b = q.id_sampletype
LEFT JOIN ref_location_80 t ON e.id_location_801=t.id_location_80 AND t.lab = {lab}
LEFT JOIN ref_location_80 w ON e.id_location_802=w.id_location_80 AND w.lab = {lab}
LEFT JOIN ref_location_80 u ON p.id_location_80_1=u.id_location_80 AND u.lab = {lab}
LEFT JOIN ref_location_80 v ON p.id_location_80_2=v.id_location_80 AND v.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['stomacher_date_conduct', 'stomacher_date_conduct'],
                    ['stomacher_barcode_food', 'stomacher_barcode_food'],
                    ['stomacher_elution_number', 'stomacher_elution_number'],
                    ['stomacher_elution', 'stomacher_elution'],
                    ['stomacher_food_weight', 'stomacher_food_weight'],
                    ['stomacher_food_comments', 'stomacher_food_comments'],
                    ['stom_idexx_in_barcode_colilert', 'stom_idexx_in_barcode_colilert'],
                    ['stom_idexx_in_barcode_falcon1', 'stom_idexx_in_barcode_falcon1'],
                    ['stom_idexx_in_volume_falcon1', 'stom_idexx_in_volume_falcon1'],
                    ['stom_idexx_in_barcode_falcon2', 'stom_idexx_in_barcode_falcon2'],
                    ['stom_idexx_in_volume_falcon2', 'stom_idexx_in_volume_falcon2'],
                    ['stom_idexx_in_dilution', 'stom_idexx_in_dilution'],
                    ['stom_idexx_in_time_incu_start', 'stom_idexx_in_time_incu_start'],
                    ['stom_idexx_in_comments', 'stom_idexx_in_comments'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['mois_initial_date_moisture', 'mois_initial_date_moisture'],
                    ['mois_initial_barcode_foil_tray', 'mois_initial_barcode_foil_tray'],
                    ['mois_initial_foil_tray_weight', 'mois_initial_foil_tray_weight'],
                    ['mois_initial_time_filter_start', 'mois_initial_time_filter_start'],
                    ['mois_initial_time_filter_finish', 'mois_initial_time_filter_finish'],
                    ['mois_initial_wet_weight', 'mois_initial_wet_weight'],
                    ['mois_initial_time_incubator', 'mois_initial_time_incubator'],
                    ['mois_initial_comments', 'mois_initial_comments'],
                    ['mois24_date_moisture', 'mois24_date_moisture'],
                    ['mois24_dry_weight24', 'mois24_dry_weight24'],
                    ['mois24_comments', 'mois24_comments'],
                    ['mois48_date_moisture', 'mois48_date_moisture'],
                    ['mois48_dry_weight48', 'mois48_dry_weight48'],
                    ['mois48_difference', 'mois48_difference'],
                    ['mois48_comments', 'mois48_comments'],
                    ['mois72_date_moisture', 'mois72_date_moisture'],
                    ['mois72_dry_weight48', 'mois72_dry_weight48'],
                    ['mois72_comments', 'mois72_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_dna1', 'metagenomics_barcode_dna1'],
                    ['metagenomics_weight_sub1', 'metagenomics_weight_sub1'],
                    ['metagenomics_barcode_storage1', 'metagenomics_barcode_storage1'],
                    ['metagenomics_position_tube1', 'metagenomics_position_tube1'],
                    ['metagenomics_dna1_location', 'metagenomics_dna1_location'],
                    ['metagenomics_barcode_dna2', 'metagenomics_barcode_dna2'],
                    ['metagenomics_weight_sub2', 'metagenomics_weight_sub2'],
                    ['metagenomics_barcode_storage2', 'metagenomics_barcode_storage2'],
                    ['metagenomics_position_tube2', 'metagenomics_position_tube2'],
                    ['metagenomics_dna2_location', 'metagenomics_dna2_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'handprint' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, g.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
e.bar_macconkey AS mac1_barcode_macconkey,
e.date_process AS mac1_date_process,
e.time_process AS mac1_time_process,
e.volume AS mac1_volume,
e.comments AS mac1_comments,
f.date_process AS mac2_date_process,
f.time_process AS mac2_time_process,
f.bar_macsweep1 AS mac2_bar_macsweep1,
f.cryobox1 AS mac2_cryobox1,
concat("F",i.freezer,"-","S",i.shelf,"-","R",i.rack,"-","DRW",i.rack_level) AS macsweep1_location,
f.bar_macsweep2 AS mac2_bar_macsweep2,
f.cryobox2 AS mac2_cryobox2,
concat("F",j.freezer,"-","S",j.shelf,"-","R",j.rack,"-","DRW",j.rack_level) AS macsweep2_location,
f.comments AS mac2_comments
FROM nhmrc_receipt a
LEFT JOIN nhmrc_mac1 e ON a.barcode_sample=e.barcode_sample
LEFT JOIN nhmrc_mac2 f ON e.bar_macconkey=f.bar_macconkey
LEFT JOIN ref_sampletype g ON a.id_type2b = g.id_sampletype
LEFT JOIN ref_location_80 i ON f.id_location_80_1=i.id_location_80 AND i.lab = {lab}
LEFT JOIN ref_location_80 j ON f.id_location_80_2=j.id_location_80 AND j.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'toys' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, q.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
g.date_weighed AS bs_before_date_weighed_micro,
g.barcode_bootsocks AS bs_before_barcode_bootsocks_micro,
g.bootsock_weight_dry AS bs_before_bootsock_weight_dry_micro,
g.comments AS bs_before_comment_micro,
h.date_weighed AS bs_after_date_weighed_micro,
h.barcode_bootsocks AS bs_after_barcode_bootsocks_micro,
h.bootsock_weight_wet AS bs_after_bootsock_weight_wet_micro,
h.comments AS bs_after_comment_micro,
b.date_conduct AS stomacher_date_conduct,
b.barcode_bootsock AS stomacher_barcode_bootsocks_Micro,
b.elution_no AS stomacher_elution_number_Micro1,
b.elution AS stomacher_elution_Micro1,
b.barcode_falcon AS stomacher_barcode_falcon_Micro1,
b.volume_stomacher AS stomacher_volume_Micro1,
c.elution_no AS stomacher_elution_number_Micro2,
c.elution AS stomacher_elution_Micro2,
c.barcode_falcon AS stomacher_barcode_falcon_Micro2,
c.volume_stomacher AS stomacher_volume_Micro2,
f.barcode_colilert AS stom_idexx_in_barcode_colilert,
f.barcode_falcon1 AS stom_idexx_in_barcode_falcon1,
f.volume_falcon1 AS stom_idexx_in_volume_falcon1,
f.barcode_falcon2 AS stom_idexx_in_barcode_falcon2,
f.volume_falcon2 AS stom_idexx_in_volume_falcon2,
f.dilution AS stom_idexx_in_dilution,
f.time_incubation AS stom_idexx_in_time_incu_start,
f.comments AS stom_idexx_in_comments,
j.date_conduct AS idexx_out_date_conduct,
j.timeout_incubation AS idexx_out_timeout_incubation,
j.time_minutes AS idexx_out_time_minutes,
j.ecoli_largewells AS idexx_out_ecoli_largewells,
j.ecoli_smallwells AS idexx_out_ecoli_smallwells,
j.ecoli_mpn AS idexx_out_ecoli_mpn,
j.coliforms_largewells AS idexx_out_coliforms_largewells,
j.coliforms_smallwells AS idexx_out_coliforms_smallwells,
j.coliforms_mpn AS idexx_out_coliforms_mpn,
j.comments AS idexx_out_comments,
k.date_moisture AS mois_initial_date_moisture,
k.barcode_foil AS mois_initial_barcode_foil_tray,
k.foil_weight AS mois_initial_foil_tray_weight,
k.time_filter_start AS mois_initial_time_filter_start,
k.time_filter_finish AS mois_initial_time_filter_finish,
k.wet_weight AS mois_initial_wet_weight,
k.time_incubator AS mois_initial_time_incubator,
k.comments AS mois_initial_comments,
l.date_moisture AS mois24_date_moisture,
l.dry_weight24 AS mois24_dry_weight24,
l.comments AS mois24_comments,
m.date_moisture AS mois48_date_moisture,
m.dry_weight48 AS mois48_dry_weight48,
m.difference AS mois48_difference,
m.comments AS mois48_comments,
n.date_moisture AS mois72_date_moisture,
n.dry_weight72 AS mois72_dry_weight48,
n.comments AS mois72_comments,
i.date_conduct AS metagenomics_date_conduct,
i.barcode_sample AS metagenomics_barcode_falcon1,
i.barcode_falcon2 AS metagenomics_barcode_falcon2,
i.volume_filtered AS metagenomics_volume_filtered,
i.time_started AS metagenomics_time_started,
i.time_finished AS metagenomics_time_finished,
i.time_minutes AS metagenomics_time_minutes,
i.barcode_dna_bag AS metagenomics_barcode_dna_bag,
i.barcode_storage AS metagenomics_barcode_storage,
concat("F",t.freezer,"-","S",t.shelf,"-","R",t.rack,"-","DRW",t.rack_level) AS metagenomics_location,
i.comments AS metagenomics_comments,
o.bar_macconkey AS mac1_barcode_macconkey,
o.date_process AS mac1_date_process,
o.time_process AS mac1_time_process,
o.volume AS mac1_volume,
o.comments AS mac1_comments,
p.date_process AS mac2_date_process,
p.time_process AS mac2_time_process,
p.bar_macsweep1 AS mac2_bar_macsweep1,
p.cryobox1 AS mac2_cryobox1,
concat("F",u.freezer,"-","S",u.shelf,"-","R",u.rack,"-","DRW",u.rack_level) AS macsweep1_location,
p.bar_macsweep2 AS mac2_bar_macsweep2,
p.cryobox2 AS mac2_cryobox2,
concat("F",v.freezer,"-","S",v.shelf,"-","R",v.rack,"-","DRW",v.rack_level) AS macsweep2_location,
p.comments AS mac2_comments
FROM nhmrc_receipt a
LEFT JOIN nhmrc_bs_stomacher b ON a.barcode_sample=b.barcode_sample AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bs_stomacher c ON a.barcode_sample=c.barcode_sample AND c.elution_no="Micro2"
LEFT JOIN nhmrc_subbs_idexx f ON b.barcode_bootsock=f.barcode_sample
LEFT JOIN nhmrc_bootsocks_before g ON b.barcode_bootsock=g.barcode_bootsocks AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bootsocks_after h ON b.barcode_bootsock=h.barcode_bootsocks AND b.elution_no="Micro1"
LEFT JOIN nhmrc_metagenomics i ON a.barcode_sample=i.barcode_sample
LEFT JOIN nhmrc_idexx2 j ON f.barcode_colilert=j.barcode_colilert
LEFT JOIN nhmrc_moisture1 k ON a.barcode_sample=k.barcode_sample
LEFT JOIN nhmrc_moisture2 l ON k.barcode_foil=l.barcode_foil
LEFT JOIN nhmrc_moisture3 m ON k.barcode_foil=m.barcode_foil
LEFT JOIN nhmrc_moisture4 n ON k.barcode_foil=n.barcode_foil
LEFT JOIN nhmrc_mac1 o ON a.barcode_sample=o.barcode_sample
LEFT JOIN nhmrc_mac2 p ON o.bar_macconkey=p.bar_macconkey
LEFT JOIN ref_sampletype q ON a.id_type2b = q.id_sampletype
LEFT JOIN ref_location_80 t ON i.id_location_80=t.id_location_80 AND t.lab = {lab}
LEFT JOIN ref_location_80 u ON p.id_location_80_1=u.id_location_80 AND u.lab = {lab}
LEFT JOIN ref_location_80 v ON p.id_location_80_2=v.id_location_80 AND v.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['bs_before_date_weighed_micro', 'bs_before_date_weighed_micro'],
                    ['bs_before_barcode_bootsocks_micro', 'bs_before_barcode_bootsocks_micro'],
                    ['bs_before_bootsock_weight_dry_micro', 'bs_before_bootsock_weight_dry_micro'],
                    ['bs_before_comment_micro', 'bs_before_comment_micro'],
                    ['bs_after_date_weighed_micro', 'bs_after_date_weighed_micro'],
                    ['bs_after_barcode_bootsocks_micro', 'bs_after_barcode_bootsocks_micro'],
                    ['bs_after_bootsock_weight_wet_micro', 'bs_after_bootsock_weight_wet_micro'],
                    ['bs_after_comment_micro', 'bs_after_comment_micro'],
                    ['stomacher_date_conduct', 'stomacher_date_conduct'],
                    ['stomacher_barcode_bootsocks_Micro', 'stomacher_barcode_bootsocks_Micro'],
                    ['stomacher_elution_number_Micro1', 'stomacher_elution_number_Micro1'],
                    ['stomacher_elution_Micro1', 'stomacher_elution_Micro1'],
                    ['stomacher_barcode_falcon_Micro1', 'stomacher_barcode_falcon_Micro1'],
                    ['stomacher_volume_Micro1', 'stomacher_volume_Micro1'],
                    ['stomacher_elution_number_Micro2', 'stomacher_elution_number_Micro2'],
                    ['stomacher_elution_Micro2', 'stomacher_elution_Micro2'],
                    ['stomacher_barcode_falcon_Micro2', 'stomacher_barcode_falcon_Micro2'],
                    ['stomacher_volume_Micro2', 'stomacher_volume_Micro2'],
                    ['stom_idexx_in_barcode_colilert', 'stom_idexx_in_barcode_colilert'],
                    ['stom_idexx_in_barcode_falcon1', 'stom_idexx_in_barcode_falcon1'],
                    ['stom_idexx_in_volume_falcon1', 'stom_idexx_in_volume_falcon1'],
                    ['stom_idexx_in_barcode_falcon2', 'stom_idexx_in_barcode_falcon2'],
                    ['stom_idexx_in_volume_falcon2', 'stom_idexx_in_volume_falcon2'],
                    ['stom_idexx_in_dilution', 'stom_idexx_in_dilution'],
                    ['stom_idexx_in_time_incu_start', 'stom_idexx_in_time_incu_start'],
                    ['stom_idexx_in_comments', 'stom_idexx_in_comments'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['mois_initial_date_moisture', 'mois_initial_date_moisture'],
                    ['mois_initial_barcode_foil_tray', 'mois_initial_barcode_foil_tray'],
                    ['mois_initial_foil_tray_weight', 'mois_initial_foil_tray_weight'],
                    ['mois_initial_time_filter_start', 'mois_initial_time_filter_start'],
                    ['mois_initial_time_filter_finish', 'mois_initial_time_filter_finish'],
                    ['mois_initial_wet_weight', 'mois_initial_wet_weight'],
                    ['mois_initial_time_incubator', 'mois_initial_time_incubator'],
                    ['mois_initial_comments', 'mois_initial_comments'],
                    ['mois24_date_moisture', 'mois24_date_moisture'],
                    ['mois24_dry_weight24', 'mois24_dry_weight24'],
                    ['mois24_comments', 'mois24_comments'],
                    ['mois48_date_moisture', 'mois48_date_moisture'],
                    ['mois48_dry_weight48', 'mois48_dry_weight48'],
                    ['mois48_difference', 'mois48_difference'],
                    ['mois48_comments', 'mois48_comments'],
                    ['mois72_date_moisture', 'mois72_date_moisture'],
                    ['mois72_dry_weight48', 'mois72_dry_weight48'],
                    ['mois72_comments', 'mois72_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_falcon1', 'metagenomics_barcode_falcon1'],
                    ['metagenomics_barcode_falcon2', 'metagenomics_barcode_falcon2'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'drinkwater' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, g.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
b.date_conduct AS idexx_in_date_conduct,
b.time_incubation AS idexx_in_time_incubation,
b.barcode_colilert AS idexx_in_barcode_colilert1,
b.volume AS idexx_in_volume1,
b.dilution AS idexx_in_dilution1,
b.comments AS idexx_in_comments1,
b.barcode_colilert2 AS idexx_in_barcode_colilert2,
b.volume2 AS idexx_in_volume2,
b.dilution2 AS idexx_in_dilution2,
b.comments2 AS idexx_in_comments2,
c.date_conduct AS idexx_out_date_conduct,
c.timeout_incubation AS idexx_out_timeout_incubation,
c.time_minutes AS idexx_out_time_minutes,
c.ecoli_largewells AS idexx_out_ecoli_largewells,
c.ecoli_smallwells AS idexx_out_ecoli_smallwells,
c.ecoli_mpn AS idexx_out_ecoli_mpn,
c.coliforms_largewells AS idexx_out_coliforms_largewells,
c.coliforms_smallwells AS idexx_out_coliforms_smallwells,
c.coliforms_mpn AS idexx_out_coliforms_mpn,
c.comments AS idexx_out_comments,
d.date_conduct AS metagenomics_date_conduct,
d.barcode_sample AS metagenomics_barcode_falcon1,
d.barcode_falcon2 AS metagenomics_barcode_falcon2,
d.volume_filtered AS metagenomics_volume_filtered,
d.time_started AS metagenomics_time_started,
d.time_finished AS metagenomics_time_finished,
d.time_minutes AS metagenomics_time_minutes,
d.barcode_dna_bag AS metagenomics_barcode_dna_bag,
d.barcode_storage AS metagenomics_barcode_storage,
concat("F",h.freezer,"-","S",h.shelf,"-","R",h.rack,"-","DRW",h.rack_level) AS metagenomics_location,
d.comments AS metagenomics_comments,
e.bar_macconkey AS mac1_barcode_macconkey,
e.date_process AS mac1_date_process,
e.time_process AS mac1_time_process,
e.volume AS mac1_volume,
e.comments AS mac1_comments,
f.date_process AS mac2_date_process,
f.time_process AS mac2_time_process,
f.bar_macsweep1 AS mac2_bar_macsweep1,
f.cryobox1 AS mac2_cryobox1,
concat("F",i.freezer,"-","S",i.shelf,"-","R",i.rack,"-","DRW",i.rack_level) AS macsweep1_location,
f.bar_macsweep2 AS mac2_bar_macsweep2,
f.cryobox2 AS mac2_cryobox2,
concat("F",j.freezer,"-","S",j.shelf,"-","R",j.rack,"-","DRW",j.rack_level) AS macsweep2_location,
f.comments AS mac2_comments
FROM nhmrc_receipt a
LEFT JOIN nhmrc_idexx1 b ON a.barcode_sample=b.barcode_sample
LEFT JOIN nhmrc_idexx2 c ON b.barcode_colilert=c.barcode_colilert
LEFT JOIN nhmrc_metagenomics d ON a.barcode_sample=d.barcode_sample
LEFT JOIN nhmrc_mac1 e ON a.barcode_sample=e.barcode_sample
LEFT JOIN nhmrc_mac2 f ON e.bar_macconkey=f.bar_macconkey
LEFT JOIN ref_sampletype g ON a.id_type2b = g.id_sampletype
LEFT JOIN ref_location_80 h ON d.id_location_80=h.id_location_80 AND h.lab = {lab}
LEFT JOIN ref_location_80 i ON f.id_location_80_1=i.id_location_80 AND i.lab = {lab}
LEFT JOIN ref_location_80 j ON f.id_location_80_2=j.id_location_80 AND j.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['idexx_in_date_conduct', 'idexx_in_date_conduct'],
                    ['idexx_in_time_incubation', 'idexx_in_time_incubation'],
                    ['idexx_in_barcode_colilert1', 'idexx_in_barcode_colilert1'],
                    ['idexx_in_volume1', 'idexx_in_volume1'],
                    ['idexx_in_dilution1', 'idexx_in_dilution1'],
                    ['idexx_in_comments1', 'idexx_in_comments1'],
                    ['idexx_in_barcode_colilert2', 'idexx_in_barcode_colilert2'],
                    ['idexx_in_volume2', 'idexx_in_volume2'],
                    ['idexx_in_dilution2', 'idexx_in_dilution2'],
                    ['idexx_in_comments2', 'idexx_in_comments2'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_falcon1', 'metagenomics_barcode_falcon1'],
                    ['metagenomics_barcode_falcon2', 'metagenomics_barcode_falcon2'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
            'swab' => [
                'sql' => <<<'SQL'
SELECT
a.barcode_sample, q.sampletype,
a.date_arrival AS reception_date_arrival,
a.time_arrival AS reception_time_arrival,
a.png_control AS reception_png_control,
a.barcode_tinytag AS reception_barcode_tinytag,
a.comments AS reception_comments,
b.date_conduct AS stomacher_date_conduct,
b.barcode_bootsock AS stomacher_barcode_bootsocks_Micro,
b.elution_no AS stomacher_elution_number_Micro1,
b.elution AS stomacher_elution_Micro1,
b.barcode_falcon AS stomacher_barcode_falcon_Micro1,
b.volume_stomacher AS stomacher_volume_Micro1,
c.elution_no AS stomacher_elution_number_Micro2,
c.elution AS stomacher_elution_Micro2,
c.barcode_falcon AS stomacher_barcode_falcon_Micro2,
c.volume_stomacher AS stomacher_volume_Micro2,
f.barcode_colilert AS stom_idexx_in_barcode_colilert,
f.barcode_falcon1 AS stom_idexx_in_barcode_falcon1,
f.volume_falcon1 AS stom_idexx_in_volume_falcon1,
f.barcode_falcon2 AS stom_idexx_in_barcode_falcon2,
f.volume_falcon2 AS stom_idexx_in_volume_falcon2,
f.dilution AS stom_idexx_in_dilution,
f.time_incubation AS stom_idexx_in_time_incu_start,
f.comments AS stom_idexx_in_comments,
j.date_conduct AS idexx_out_date_conduct,
j.timeout_incubation AS idexx_out_timeout_incubation,
j.time_minutes AS idexx_out_time_minutes,
j.ecoli_largewells AS idexx_out_ecoli_largewells,
j.ecoli_smallwells AS idexx_out_ecoli_smallwells,
j.ecoli_mpn AS idexx_out_ecoli_mpn,
j.coliforms_largewells AS idexx_out_coliforms_largewells,
j.coliforms_smallwells AS idexx_out_coliforms_smallwells,
j.coliforms_mpn AS idexx_out_coliforms_mpn,
j.comments AS idexx_out_comments,
i.date_conduct AS metagenomics_date_conduct,
i.barcode_sample AS metagenomics_barcode_falcon1,
i.barcode_falcon2 AS metagenomics_barcode_falcon2,
i.volume_filtered AS metagenomics_volume_filtered,
i.time_started AS metagenomics_time_started,
i.time_finished AS metagenomics_time_finished,
i.time_minutes AS metagenomics_time_minutes,
i.barcode_dna_bag AS metagenomics_barcode_dna_bag,
i.barcode_storage AS metagenomics_barcode_storage,
concat("F",t.freezer,"-","S",t.shelf,"-","R",t.rack,"-","DRW",t.rack_level) AS metagenomics_location,
i.comments AS metagenomics_comments,
o.bar_macconkey AS mac1_barcode_macconkey,
o.barcode_falcon2 AS mac1_barcode_falcon2,
o.date_process AS mac1_date_process,
o.time_process AS mac1_time_process,
o.volume AS mac1_volume,
o.comments AS mac1_comments,
p.date_process AS mac2_date_process,
p.time_process AS mac2_time_process,
p.bar_macsweep1 AS mac2_bar_macsweep1,
p.cryobox1 AS mac2_cryobox1,
concat("F",u.freezer,"-","S",u.shelf,"-","R",u.rack,"-","DRW",u.rack_level) AS macsweep1_location,
p.bar_macsweep2 AS mac2_bar_macsweep2,
p.cryobox2 AS mac2_cryobox2,
concat("F",v.freezer,"-","S",v.shelf,"-","R",v.rack,"-","DRW",v.rack_level) AS macsweep2_location,
p.comments AS mac2_comments

FROM nhmrc_receipt a
LEFT JOIN nhmrc_bs_stomacher b ON a.barcode_sample=b.barcode_sample AND b.elution_no="Micro1"
LEFT JOIN nhmrc_bs_stomacher c ON a.barcode_sample=c.barcode_sample AND c.elution_no="Micro2"
LEFT JOIN nhmrc_subbs_idexx f ON b.barcode_bootsock=f.barcode_sample
LEFT JOIN nhmrc_metagenomics i ON a.barcode_sample=i.barcode_sample
LEFT JOIN nhmrc_idexx2 j ON f.barcode_colilert=j.barcode_colilert
LEFT JOIN nhmrc_mac1 o ON a.barcode_sample=o.barcode_sample
LEFT JOIN nhmrc_mac2 p ON o.bar_macconkey=p.bar_macconkey
LEFT JOIN ref_sampletype q ON a.id_type2b = q.id_sampletype
LEFT JOIN ref_location_80 t ON i.id_location_80=t.id_location_80 AND t.lab = {lab}
LEFT JOIN ref_location_80 u ON p.id_location_80_1=u.id_location_80 AND u.lab = {lab}
LEFT JOIN ref_location_80 v ON p.id_location_80_2=v.id_location_80 AND v.lab = {lab}
WHERE a.id_type2b = {rep}
AND (a.date_arrival >= {d1}
    AND a.date_arrival <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_arrival DESC, a.time_arrival ASC
SQL,
                'columns' => [
                    ['barcode_sample', 'barcode_sample'],
                    ['sampletype', 'sampletype'],
                    ['reception_date_arrival', 'reception_date_arrival'],
                    ['reception_time_arrival', 'reception_time_arrival'],
                    ['reception_png_control', 'reception_png_control'],
                    ['reception_barcode_tinytag', 'reception_barcode_tinytag'],
                    ['reception_comments', 'reception_comments'],
                    ['stomacher_date_conduct', 'stomacher_date_conduct'],
                    ['stomacher_barcode_bootsocks_Micro', 'stomacher_barcode_bootsocks_Micro'],
                    ['stomacher_elution_number_Micro1', 'stomacher_elution_number_Micro1'],
                    ['stomacher_elution_Micro1', 'stomacher_elution_Micro1'],
                    ['stomacher_barcode_falcon_Micro1', 'stomacher_barcode_falcon_Micro1'],
                    ['stomacher_volume_Micro1', 'stomacher_volume_Micro1'],
                    ['stomacher_elution_number_Micro2', 'stomacher_elution_number_Micro2'],
                    ['stomacher_elution_Micro2', 'stomacher_elution_Micro2'],
                    ['stomacher_barcode_falcon_Micro2', 'stomacher_barcode_falcon_Micro2'],
                    ['stomacher_volume_Micro2', 'stomacher_volume_Micro2'],
                    ['stom_idexx_in_barcode_colilert', 'stom_idexx_in_barcode_colilert'],
                    ['stom_idexx_in_barcode_falcon1', 'stom_idexx_in_barcode_falcon1'],
                    ['stom_idexx_in_volume_falcon1', 'stom_idexx_in_volume_falcon1'],
                    ['stom_idexx_in_barcode_falcon2', 'stom_idexx_in_barcode_falcon2'],
                    ['stom_idexx_in_volume_falcon2', 'stom_idexx_in_volume_falcon2'],
                    ['stom_idexx_in_dilution', 'stom_idexx_in_dilution'],
                    ['stom_idexx_in_time_incu_start', 'stom_idexx_in_time_incu_start'],
                    ['stom_idexx_in_comments', 'stom_idexx_in_comments'],
                    ['idexx_out_date_conduct', 'idexx_out_date_conduct'],
                    ['idexx_out_timeout_incubation', 'idexx_out_timeout_incubation'],
                    ['idexx_out_time_minutes', 'idexx_out_time_minutes'],
                    ['idexx_out_ecoli_largewells', 'idexx_out_ecoli_largewells'],
                    ['idexx_out_ecoli_smallwells', 'idexx_out_ecoli_smallwells'],
                    ['idexx_out_ecoli_mpn', 'idexx_out_ecoli_mpn'],
                    ['idexx_out_coliforms_largewells', 'idexx_out_coliforms_largewells'],
                    ['idexx_out_coliforms_smallwells', 'idexx_out_coliforms_smallwells'],
                    ['idexx_out_coliforms_mpn', 'idexx_out_coliforms_mpn'],
                    ['idexx_out_comments', 'idexx_out_comments'],
                    ['metagenomics_date_conduct', 'metagenomics_date_conduct'],
                    ['metagenomics_barcode_falcon1', 'metagenomics_barcode_falcon1'],
                    ['metagenomics_barcode_falcon2', 'metagenomics_barcode_falcon2'],
                    ['metagenomics_volume_filtered', 'metagenomics_volume_filtered'],
                    ['metagenomics_time_started', 'metagenomics_time_started'],
                    ['metagenomics_time_finished', 'metagenomics_time_finished'],
                    ['metagenomics_time_minutes', 'metagenomics_time_minutes'],
                    ['metagenomics_barcode_dna_bag', 'metagenomics_barcode_dna_bag'],
                    ['metagenomics_barcode_storage', 'metagenomics_barcode_storage'],
                    ['metagenomics_location', 'metagenomics_location'],
                    ['metagenomics_comments', 'metagenomics_comments'],
                    ['mac1_barcode_macconkey', 'mac1_barcode_macconkey'],
                    ['mac1_barcode_falcon2', 'mac1_barcode_falcon2'],
                    ['mac1_date_process', 'mac1_date_process'],
                    ['mac1_time_process', 'mac1_time_process'],
                    ['mac1_volume', 'mac1_volume'],
                    ['mac1_comments', 'mac1_comments'],
                    ['mac2_date_process', 'mac2_date_process'],
                    ['mac2_time_process', 'mac2_time_process'],
                    ['mac2_bar_macsweep1', 'mac2_bar_macsweep1'],
                    ['mac2_cryobox1', 'mac2_cryobox1'],
                    ['macsweep1_location', 'macsweep1_location'],
                    ['mac2_bar_macsweep2', 'mac2_bar_macsweep2'],
                    ['mac2_cryobox2', 'mac2_cryobox2'],
                    ['macsweep2_location', 'macsweep2_location'],
                    ['mac2_comments', 'mac2_comments'],
                ],
            ],
        ],
        'preview' => [
            'columns' => [
                'barcode_sample' => 'Barcode sample',
                'date_arrival' => 'Date arrive',
                'time_arrival' => 'Time arrive',
                'sampletype' => 'Sample type',
                'png_control' => 'P&G control',
                'barcode_tinytag' => 'Barcode tinytag',
            ],
            'sql' => <<<'SQL'
SELECT a.barcode_sample, a.date_arrival, a.time_arrival, b.sampletype, a.png_control, a.barcode_tinytag
FROM nhmrc_receipt a
LEFT JOIN ref_sampletype b ON a.id_type2b = b.id_sampletype
WHERE {type_where}
  AND a.lab = {lab} AND a.flag = 0
  AND a.date_arrival >= {d1} AND a.date_arrival <= {d2}
ORDER BY a.date_arrival DESC, a.time_arrival DESC, a.barcode_sample ASC
LIMIT 20000
SQL,
        ],
    ],

    // ============================================================
    'rep_frez' => [
        'title' => 'REPORTS - Freezer REPORT',
        'buttons' => [
            [
                'label' => 'Freezer IN',
                'module' => 'freezer_in',
            ],
            [
                'label' => 'Freezer OUT',
                'module' => 'freezer_out',
            ],
            [
                'label' => 'Sample Used Up',
                'module' => 'freezer_out',
                'variant' => 'usedup',
            ],
        ],
    ],

    // ============================================================
    'rep_se' => [
        'title' => 'REPORTS - Sample External REPORT',
        'buttons' => [
            [
                'label' => 'Sample Reception',
                'module' => 'se_sample_reception',
            ],
            [
                'label' => 'Sample Analysis Summary',
                'module' => 'se_sample_analysis',
            ],
            [
                'label' => 'Sample Result',
                'module' => 'se_sample_result',
            ],
            [
                'label' => 'Sample Ended',
                'module' => 'se_sample_ended',
            ],
            [
                'label' => 'Sample Sent to Other Lab',
                'module' => 'se_sample_sent_other',
            ],
        ],
        'filename' => 'ALL_SampleExternal_reports',
        'sheets' => [
            [
                'name' => 'Sample_Reception',
                'sql' => <<<'SQL'
SELECT barcode_sample AS Barcode_sample, new_barcode AS New_barcode,
date_received AS Date_received, lab_received AS Lab_received, person AS Person,
sample_type AS Sample_type, obtained AS Obtained, conditions AS Conditions,
quarantine AS Quarantine, permit_number AS Permit_number,
name_email_custodian AS Name_email_custodian, desc_storage AS Desc_storage,
loc_storage AS Loc_storage, TRIM(comments) AS Comments
FROM se_sample_receipt
WHERE  (date_received >= {d1}
AND date_received <= {d2})
AND lab = {lab}
AND flag = 0
ORDER BY date_received, barcode_sample ASC
SQL,
                'columns' => ['Barcode_sample', 'New_barcode', 'Date_received', 'Lab_received', 'Person', 'Sample_type', 'Obtained', 'Conditions', 'Quarantine', 'Permit_number', 'Name_email_custodian', 'Desc_storage', 'Loc_storage', 'Comments'],
            ],
            [
                'name' => 'Sample_Analysis_Summary',
                'sql' => <<<'SQL'
SELECT barcode_sample AS Barcode_sample, date_analysis AS Date_analysis,
analysis AS Analysis, person AS Person, TRIM(comments) AS Comments
FROM se_analysis
WHERE (date_analysis >= {d1}
AND date_analysis <= {d2})
AND lab = {lab}
AND flag = 0
ORDER BY date_analysis, barcode_sample ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_analysis', 'Analysis', 'Person', 'Comments'],
            ],
            [
                'name' => 'Sample_Result',
                'sql' => <<<'SQL'
SELECT barcode_sample AS Barcode_sample, date_analysis AS Date_analysis,
analyte AS Analyte, result AS Result, units AS Units, person AS Person, TRIM(comments) AS Comments
FROM se_result
WHERE (date_analysis >= {d1}
AND date_analysis <= {d2})
AND lab = {lab}
AND flag = 0
ORDER BY date_analysis, barcode_sample ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_analysis', 'Analyte', 'Result', 'Units', 'Person', 'Comments'],
            ],
            [
                'name' => 'Sample_Ended',
                'sql' => <<<'SQL'
SELECT barcode_sample AS Barcode_sample, date_ended AS Date_ended,
lab_sample_end AS Lab_sample_end, TRIM(comments) AS Comments
FROM se_sample_ended
WHERE (date_ended >= {d1}
AND date_ended <= {d2})
AND lab = {lab}
AND flag = 0
ORDER BY date_ended, barcode_sample ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_ended', 'Lab_sample_end', 'Comments'],
            ],
            [
                'name' => 'Sample_Sent_to_Other_Lab',
                'sql' => <<<'SQL'
SELECT barcode_sample AS Barcode_sample, date_shipped AS Date_shipped,
volume AS Volume, destination AS Destination, custodian AS Custodian, TRIM(comments) AS Comments
FROM se_sample_sent
WHERE (date_shipped >= {d1}
AND date_shipped <= {d2})
AND lab = {lab}
AND flag = 0
ORDER BY date_shipped, barcode_sample ASC
SQL,
                'columns' => ['Barcode_sample', 'Date_shipped', 'Volume', 'Destination', 'Custodian', 'Comments'],
            ],
        ],
        'preview' => [
            'columns' => [
                'barcode_sample' => 'Barcode sample',
                'new_barcode' => 'New barcode',
                'date_received' => 'Date received',
                'lab_received' => 'Lab received',
                'sample_type' => 'Sample type',
                'obtained' => 'Obtained from',
                'conditions' => 'Condition',
                'comments' => 'Comments',
            ],
            'sql' => <<<'SQL'
SELECT barcode_sample, new_barcode, date_received, lab_received, sample_type, obtained, conditions, TRIM(comments) AS comments
FROM se_sample_receipt
WHERE lab = {lab} AND flag = 0
  AND date_received >= {d1} AND date_received <= {d2}
ORDER BY date_received DESC, barcode_sample ASC
LIMIT 20000
SQL,
        ],
    ],

    // ============================================================
    'rep_dna' => [
        'title' => 'REPORTS - DNA REPORT',
        'buttons' => [
            ['label' => 'DNA Sample Control',     'module' => 'dna_sample_control'],
            ['label' => 'DNA Extraction',         'module' => 'dna_extraction'],
            ['label' => 'DNA Concentration',      'module' => 'dna_concentration'],
            ['label' => 'DNA Aliquotting',        'module' => 'dna_aliquotting'],
            ['label' => 'DNA Sample Analysis',    'module' => 'dna_sample_analysis'],
            ['label' => 'DNA Nanopore Analysis',  'module' => 'dna_nanopore_analysis'],   // tampil setelah modulnya dibuat
            ['label' => 'DNA Nanopore Result',    'module' => 'dna_nanopore_result'],
        ],
        'filename' => 'ALL_DNA_reports',
        'sheets' => [
            [
                'name' => 'DNA_Extraction',
                'sql' => <<<'SQL'
SELECT a.barcode_dna AS Barcode_DNA, a.barcode_sample AS Source_Barcode_sample,
a.date_extraction AS Date_extraction, b.initial AS Lab_tech,
a.kit_lot AS Kit_lot, a.sampletype AS Sample_type,
c.Barcode_sample AS Parent_Barcode_Sample, c.sampletype AS Parent_Sample_Type,
a.weights AS Weights, a.tube_number AS Tube_number, a.cryobox AS Cryobox,
a.barcode_metagenomics AS Barcode_metagenomics,
CONCAT('F', d.freezer, '-', 'S', d.shelf, '-', 'R', d.rack, '-', 'DRW', d.rack_level) AS Location,
a.meta_box AS Meta_box, a.qc_status AS QC_Status, a.comments AS Comments
FROM dna_extraction a
LEFT JOIN ref_person b ON a.id_person = b.id_person
LEFT JOIN (SELECT * FROM v_dna_all_sample WHERE lab = {lab}) c ON a.barcode_sample = c.barcode
LEFT JOIN (SELECT fi.barcode_sample, fi.id_location_80
             FROM freezer_in fi
             JOIN (SELECT barcode_sample, MIN(CONCAT(date_in, time_in)) AS m
                     FROM freezer_in WHERE lab = {lab} GROUP BY barcode_sample) fm
               ON fi.barcode_sample = fm.barcode_sample AND CONCAT(fi.date_in, fi.time_in) = fm.m
            WHERE fi.lab = {lab}) g ON a.barcode_dna = g.barcode_sample
LEFT JOIN ref_location_80 d ON g.id_location_80 = d.id_location_80 AND d.lab = {lab}
WHERE a.lab = {lab}
AND (a.date_extraction >= {d1} AND a.date_extraction <= {d2})
AND a.flag = 0
ORDER BY a.date_extraction, a.barcode_dna ASC
SQL,
                'columns' => ['Barcode_DNA', 'Source_Barcode_sample', 'Date_extraction', 'Lab_tech', 'Kit_lot', 'Sample_type',
                              'Parent_Barcode_Sample', 'Parent_Sample_Type', 'Weights', 'Tube_number', 'Cryobox',
                              'Barcode_metagenomics', 'Location', 'Meta_box', 'QC_Status', 'Comments'],
            ],
            [
                'name' => 'DNA_Concentration',
                'sql' => <<<'SQL'
SELECT a.barcode_dna AS Barcode_DNA, a.date_concentration AS Date_concentration,
c.initial AS Lab_tech, b.sampletype AS Sample_type, a.dna_concentration AS DNA_concentration,
TRIM(a.comments) AS Comments
FROM dna_concentration a
LEFT JOIN dna_extraction b ON a.barcode_dna = b.barcode_dna AND b.lab = {lab} AND b.flag = 0
LEFT JOIN ref_person c ON a.id_person = c.id_person
WHERE (a.date_concentration >= {d1} AND a.date_concentration <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_concentration, a.barcode_dna ASC
SQL,
                'columns' => ['Barcode_DNA', 'Date_concentration', 'Lab_tech', 'Sample_type', 'DNA_concentration', 'Comments'],
            ],
            [
                'name' => 'DNA_Aliquotting',
                'sql' => <<<'SQL'
SELECT c.barcode_dna AS Barcode_DNA, a.date_aliquot AS Date_aliquot, b.initial AS Lab_tech,
a.barcode_monash AS Barcode_Monash, a.barcode_cambridge AS Barcode_Cambridge,
c.row_id AS Row_ID, c.column_id AS Column_ID, TRIM(a.comments) AS Comments
FROM dna_aliquot a
LEFT JOIN dna_aliquot_det c ON a.id_dna = c.id_dna AND c.lab = {lab} AND c.flag = 0
LEFT JOIN ref_person b ON a.id_person = b.id_person
WHERE (a.date_aliquot >= {d1} AND a.date_aliquot <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_aliquot, a.barcode_monash, c.row_id, c.column_id ASC
SQL,
                'columns' => ['Barcode_DNA', 'Date_aliquot', 'Lab_tech', 'Barcode_Monash', 'Barcode_Cambridge',
                              'Row_ID', 'Column_ID', 'Comments'],
            ],
            [
                'name' => 'DNA_Sample_Analysis',
                'sql' => <<<'SQL'
SELECT a.barcode_dna AS Barcode_DNA, a.date_analysis AS Date_analysis, b.initial AS Lab_tech,
a.analysis_type AS Analysis_type, a.run_number AS Run_number,
a.barcode_array AS Barcode_array, TRIM(a.comments) AS Comments
FROM dna_sample_analysis a
LEFT JOIN ref_person b ON a.id_person = b.id_person
WHERE (a.date_analysis >= {d1} AND a.date_analysis <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_analysis, a.barcode_dna, a.analysis_type, a.run_number ASC
SQL,
                'columns' => ['Barcode_DNA', 'Date_analysis', 'Lab_tech', 'Analysis_type', 'Run_number', 'Barcode_array', 'Comments'],
            ],
            [
                'name' => 'DNA_Nanopore_Analysis',
                'sql' => <<<'SQL'
SELECT a.barcode_dna AS Barcode_DNA, a.date_analysis AS Date_analysis, b.initial AS Lab_tech,
a.barcode_id AS Barcode_ID
FROM dna_nanopore_analysis a
LEFT JOIN ref_person b ON a.id_person = b.id_person
WHERE (a.date_analysis >= {d1} AND a.date_analysis <= {d2})
AND a.lab = {lab}
AND a.flag = 0
ORDER BY a.date_analysis, a.barcode_dna, a.barcode_id ASC
SQL,
                'columns' => ['Barcode_DNA', 'Date_analysis', 'Lab_tech', 'Barcode_ID'],
            ],
        ],
        'preview' => [
            'columns' => [
                'barcode_sample'       => 'Barcode_sample',
                'barcode_dna'          => 'Barcode DNA',
                'date_extraction'      => 'Date extraction',
                'cryobox'              => 'Cryobox',
                'barcode_metagenomics' => 'Barcode meta',
                'meta_box'             => 'Meta box',
                'comments'             => 'Comments',
            ],
            'sql' => <<<'SQL'
SELECT a.barcode_sample, a.barcode_dna, a.date_extraction, a.cryobox, a.barcode_metagenomics, a.meta_box, a.comments
FROM dna_extraction a
WHERE a.lab = {lab} AND a.flag = 0
  AND a.date_extraction >= {d1} AND a.date_extraction <= {d2}
ORDER BY a.date_extraction DESC, a.barcode_sample ASC, a.barcode_dna ASC
LIMIT 20000
SQL,
        ],
    ],    
];
