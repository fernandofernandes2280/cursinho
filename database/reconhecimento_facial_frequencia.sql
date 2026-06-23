ALTER TABLE frequencia
  ADD COLUMN comparacaoFacialResultado VARCHAR(30) NULL,
  ADD COLUMN comparacaoFacialPontuacao DECIMAL(5,2) NULL,
  ADD COLUMN comparacaoFacialDetalhes TEXT NULL,
  ADD COLUMN comparacaoFacialData DATETIME NULL;
