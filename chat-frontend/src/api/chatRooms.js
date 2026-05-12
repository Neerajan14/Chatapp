import api from "../lib/axios";

export const getChatRooms = async () => {
    const res = await api.get("/chat-rooms");
    return res.data;
};