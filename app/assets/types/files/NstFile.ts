export interface NstFileTag {
   id: number;
   name: string;
}
export interface NstFile {
   id: number;
   filename: string;
   type: string;
   route: string;
   tag: NstFileTag;
}
