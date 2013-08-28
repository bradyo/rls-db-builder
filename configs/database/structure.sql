SET storage_engine=INNODB;

CREATE TABLE IF NOT EXISTS gene (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ncbi_gene_id INT,
    ncbi_taxon_id INT,
    symbol VARCHAR(32),
    locus_tag VARCHAR(32),
    INDEX (ncbi_gene_id),
    INDEX (ncbi_taxon_id),
    INDEX (symbol),
    INDEX (locus_tag)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS gene_synonym (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gene_id INT NOT NULL,
    synonym VARCHAR(64) NOT NULL,
    INDEX (synonym),
    FOREIGN KEY (gene_id) REFERENCES gene (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS build_meta (
    created_at DATETIME NOT NULL
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS experiment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_email VARCHAR(128),
    name VARCHAR(128),
    INDEX (name)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS citation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pubmed_id INT,
    title TEXT,
    first_author TEXT,
    year INT,
    summary TEXT,
    INDEX (pubmed_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS strain (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    contact_email VARCHAR(128),
    background VARCHAR(255),
    mating_type VARCHAR(255),
    genotype VARCHAR(255),
    short_genotype VARCHAR(255),
    pooling_genotype VARCHAR(255),
    comment TEXT,
    INDEX (name),
    INDEX (background),
    INDEX (mating_type),
    INDEX (short_genotype),
    INDEX (pooling_genotype)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS cell (
    id INT AUTO_INCREMENT PRIMARY KEY,
    experiment_id INT,
    label VARCHAR(255),
    strain_id INT,
    media VARCHAR(255),
    temperature VARCHAR(255),
    lifespan INT,
    end_state VARCHAR(32),
    INDEX (media),
    INDEX (temperature),
    INDEX (lifespan),
    INDEX (end_state),
    FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE SET NULL,
    FOREIGN KEY (strain_id) REFERENCES strain (id) ON DELETE SET NULL
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS cell_citation (
    cell_id INT NOT NULL,
    citation_id INT NOT NULL,
    FOREIGN KEY (cell_id) REFERENCES cell (id) ON DELETE CASCADE,
    FOREIGN KEY (citation_id) REFERENCES citation (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS sample (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pooled_by VARCHAR(32),
    label VARCHAR(255),
    strain_name VARCHAR(64),
    background VARCHAR(64),
    mating_type VARCHAR(32),
    genotype VARCHAR(255),
    media VARCHAR(255),
    temperature DECIMAL(5,3),
    lifespans TEXT,
    lifespans_count INT,
    lifespans_omitted_count INT,
    lifespans_mean DOUBLE,
    lifespans_median DOUBLE,
    lifespans_stdev DOUBLE,
    end_states TEXT,
    INDEX (pooled_by),
    INDEX (strain_name),
    INDEX (background),
    INDEX (mating_type),
    INDEX (temperature),
    INDEX (lifespans_count),
    INDEX (lifespans_mean),
    INDEX (lifespans_median)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS sample_cell (
    sample_id INT NOT NULL,
    cell_id INT NOT NULL,
    FOREIGN KEY (sample_id) REFERENCES sample (id) ON DELETE CASCADE,
    FOREIGN KEY (cell_id) REFERENCES cell (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS comparison (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pooled_by VARCHAR(32),
    test_sample_id INT NOT NULL,
    reference_sample_id INT NOT NULL,
    percent_change DOUBLE,
    ranksum_u DOUBLE,
    ranksum_p DOUBLE,
    INDEX (pooled_by),
    INDEX (percent_change),
    INDEX (ranksum_p),
    FOREIGN KEY (test_sample_id) REFERENCES sample (id) ON DELETE CASCADE,
    FOREIGN KEY (reference_sample_id) REFERENCES sample (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS across_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    locus_tag VARCHAR(32),
    genotype VARCHAR(255) NOT NULL,
    background VARCHAR(64),
    mating_type VARCHAR(32),
    temperature DOUBLE,
    ypd_comparison_id INT,
    d05_comparison_id INT,
    d005_comparison_id INT,
    gly3_comparison_id INT,
    FOREIGN KEY (ypd_comparison_id) REFERENCES comparison (id) ON DELETE SET NULL,
    FOREIGN KEY (d05_comparison_id) REFERENCES comparison (id) ON DELETE SET NULL,
    FOREIGN KEY (d005_comparison_id) REFERENCES comparison (id) ON DELETE SET NULL,
    FOREIGN KEY (gly3_comparison_id) REFERENCES comparison (id) ON DELETE SET NULL
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS across_mating_type (
    id INT AUTO_INCREMENT PRIMARY KEY,
    locus_tag VARCHAR(32),
    genotype VARCHAR(255) NOT NULL,
    background VARCHAR(64),
    media VARCHAR(128),
    temperature REAL,
    a_comparison_id INT,
    alpha_comparison_id INT,
    INDEX (locus_tag),
    INDEX (genotype),
    INDEX (background),
    INDEX (media),
    INDEX (temperature),
    FOREIGN KEY (a_comparison_id) REFERENCES comparison (id) ON DELETE SET NULL,
    FOREIGN KEY (alpha_comparison_id) REFERENCES comparison (id) ON DELETE SET NULL
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

