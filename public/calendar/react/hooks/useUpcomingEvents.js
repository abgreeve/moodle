/* global require */
import { useEffect, useState } from 'react';

let ajaxLoader;
let notificationLoader;

const loadAjax = () => {
    if (!ajaxLoader) {
        ajaxLoader = new Promise((resolve, reject) => {
            require(['core/ajax'], (Ajax) => resolve(Ajax), (error) => reject(error));
        });
    }
    return ajaxLoader;
};

const loadNotification = async () => {
    if (!notificationLoader) {
        notificationLoader = new Promise((resolve, reject) => {
            require(['core/notification'], (Notification) => resolve(Notification), (error) => reject(error));
        });
    }
    return notificationLoader;
};

const normaliseUpcomingEvents = (context) => {
    if (!context || !Array.isArray(context.events)) {
        return [];
    }

    return context.events.map((event) => ({
        id: event.id,
        title: event.name,
        start: new Date(event.timestart * 1000),
        end: new Date((event.timestart + event.timeduration) * 1000),
    }));
};

export const useUpcomingEvents = (courseId = 0, categoryId = 0) => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        let mounted = true;

        const fetchEvents = async () => {
            setLoading(true);
            setError(null);

            try {
                const Ajax = await loadAjax();
                const request = {
                    methodname: 'core_calendar_get_calendar_upcoming_view',
                    args: {
                        courseid: Number(courseId) || 0,
                        categoryid: Number(categoryId) || 0,
                    },
                };

                const context = await Ajax.call([request])[0];
                if (!mounted) {
                    return;
                }

                window.console.log(context);

                setEvents(normaliseUpcomingEvents(context));
            } catch (err) {
                if (!mounted) {
                    return;
                }

                setError(err);

                try {
                    const Notification = await loadNotification();
                    Notification.exception(err);
                } catch (notificationError) {
                    window.console.error('Failed to display notification for calendar error.', notificationError);
                }
            } finally {
                if (mounted) {
                    setLoading(false);
                }
            }
        };

        fetchEvents();

        return () => {
            mounted = false;
        };
    }, [courseId, categoryId]);

    return { events, loading, error };
};

export default useUpcomingEvents;
