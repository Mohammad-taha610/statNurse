import {Provider} from './Provider';

interface NstMemberUser {
    id: number;
    name: string;
    type: string;
    providers: Provider[];
    roles: string[];
}

export default NstMemberUser;

