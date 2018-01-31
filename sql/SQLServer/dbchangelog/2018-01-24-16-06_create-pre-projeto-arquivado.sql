CREATE TABLE sac.dbo.PreProjetoArquivado (
       idPreProjetoArquivado int NOT NULL IDENTITY(1,1)
       idPreProjeto int NOT NULL,
       MotivoArquivamento varchar(MAX),
       SolicitacaoDesarquivamento varchar(MAX),
       Avaliacao varchar(MAX),
       idAvaliador int NOT NULL,
       dtArquivamento datetime,
       dtSolicitacaoDesarquivamento datetime,
       dtAvaliacao datetime,
       stEstado int,
       stDecisao int
)
