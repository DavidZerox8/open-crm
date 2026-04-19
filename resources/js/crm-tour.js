import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

const ROUTE_TO_MODULE = [
    [/^crm\.dashboard$/, 'dashboard'],
    [/^crm\.leads\./, 'leads'],
    [/^crm\.companies\./, 'companies'],
    [/^crm\.contacts\./, 'contacts'],
    [/^crm\.pipeline\./, 'pipeline'],
    [/^crm\.deals\./, 'deals'],
    [/^crm\.tasks\./, 'tasks'],
    [/^crm\.reports\./, 'reports'],
];

const I18N = {
    en: {
        next: 'Next',
        prev: 'Back',
        done: 'Finish',
        progressText: 'Step {{current}} of {{total}}',
        modules: {
            dashboard: [
                {
                    element: '[data-tour="crm-sidebar"]',
                    popover: {
                        title: 'CRM navigation',
                        description: 'Use this sidebar to move across every CRM module quickly.',
                    },
                },
                {
                    element: '[data-tour="dashboard-kpis"]',
                    popover: {
                        title: 'Key metrics',
                        description: 'These cards summarize open deals, wins, pipeline value, and pending tasks.',
                    },
                },
                {
                    element: '[data-tour="dashboard-activity"]',
                    popover: {
                        title: 'Recent activity',
                        description: 'Review timeline activity to understand what changed recently.',
                    },
                },
                {
                    element: '[data-tour="dashboard-tasks"]',
                    popover: {
                        title: 'Upcoming tasks',
                        description: 'Track what is next and jump directly to the task center.',
                    },
                },
            ],
            leads: [
                {
                    element: '[data-tour="leads-filters"]',
                    popover: {
                        title: 'Lead filters',
                        description: 'Filter leads by text and status to focus on the right opportunities.',
                    },
                },
                {
                    element: '[data-tour="leads-table"]',
                    popover: {
                        title: 'Lead list',
                        description: 'Open any row to inspect details, activities, and conversion options.',
                    },
                },
                {
                    element: '[data-tour="leads-create"]',
                    popover: {
                        title: 'Create lead',
                        description: 'Use this action to register a new lead and start qualification.',
                    },
                },
                {
                    element: '[data-tour="lead-details"]',
                    popover: {
                        title: 'Lead profile',
                        description: 'Use this panel to inspect source data, score, and conversion details.',
                    },
                },
                {
                    element: '[data-tour="lead-activity-form"]',
                    popover: {
                        title: 'Lead activity',
                        description: 'Log notes, calls, or meetings to keep your sales memory complete.',
                    },
                },
            ],
            companies: [
                {
                    element: '[data-tour="companies-filters"]',
                    popover: {
                        title: 'Company search',
                        description: 'Search by company name, industry, or email domain.',
                    },
                },
                {
                    element: '[data-tour="companies-table"]',
                    popover: {
                        title: 'Company list',
                        description: 'Review related contacts and deals directly from this table.',
                    },
                },
                {
                    element: '[data-tour="companies-create"]',
                    popover: {
                        title: 'Create company',
                        description: 'Add a company to centralize account context for contacts and deals.',
                    },
                },
                {
                    element: '[data-tour="company-details"]',
                    popover: {
                        title: 'Company detail',
                        description: 'Review account profile, channels, and key business context here.',
                    },
                },
                {
                    element: '[data-tour="company-deals"]',
                    popover: {
                        title: 'Company deals',
                        description: 'Track related opportunities and open each deal for execution.',
                    },
                },
            ],
            contacts: [
                {
                    element: '[data-tour="contacts-filters"]',
                    popover: {
                        title: 'Contact search',
                        description: 'Find contacts by name, email, or job title.',
                    },
                },
                {
                    element: '[data-tour="contacts-table"]',
                    popover: {
                        title: 'Contact list',
                        description: 'Open a contact to see linked company, deals, and activity history.',
                    },
                },
                {
                    element: '[data-tour="contacts-create"]',
                    popover: {
                        title: 'Create contact',
                        description: 'Capture new decision makers and attach them to companies.',
                    },
                },
                {
                    element: '[data-tour="contact-details"]',
                    popover: {
                        title: 'Contact detail',
                        description: 'See role, owner, and direct contact data in one place.',
                    },
                },
                {
                    element: '[data-tour="contact-deals"]',
                    popover: {
                        title: 'Contact deals',
                        description: 'Follow the opportunities linked to this contact.',
                    },
                },
            ],
            pipeline: [
                {
                    element: '[data-tour="pipeline-selector"]',
                    popover: {
                        title: 'Pipeline selector',
                        description: 'Switch between pipelines to inspect different sales flows.',
                    },
                },
                {
                    element: '[data-tour="pipeline-board"]',
                    popover: {
                        title: 'Kanban board',
                        description: 'Each column is a stage. Move deals to keep forecast and status current.',
                    },
                },
            ],
            deals: [
                {
                    element: '[data-tour="deal-stage-panel"]',
                    popover: {
                        title: 'Deal progression',
                        description: 'Move stages and close deals as won or lost with full traceability.',
                    },
                },
                {
                    element: '[data-tour="deal-tasks"]',
                    popover: {
                        title: 'Deal tasks',
                        description: 'Track deal-related execution tasks and due dates.',
                    },
                },
                {
                    element: '[data-tour="deal-activity-form"]',
                    popover: {
                        title: 'Activity logging',
                        description: 'Register calls, emails, notes, and meetings on the deal timeline.',
                    },
                },
            ],
            tasks: [
                {
                    element: '[data-tour="tasks-filters"]',
                    popover: {
                        title: 'Task filters',
                        description: 'Filter by status and priority to drive daily execution.',
                    },
                },
                {
                    element: '[data-tour="tasks-table"]',
                    popover: {
                        title: 'Task board',
                        description: 'Complete pending tasks and monitor assignments from one place.',
                    },
                },
                {
                    element: '[data-tour="tasks-create"]',
                    popover: {
                        title: 'Create task',
                        description: 'Plan follow-ups and responsibilities with due dates.',
                    },
                },
            ],
            reports: [
                {
                    element: '[data-tour="reports-kpis"]',
                    popover: {
                        title: 'Performance KPIs',
                        description: 'Track conversion, won value, open value, and lead volume.',
                    },
                },
                {
                    element: '[data-tour="reports-funnel"]',
                    popover: {
                        title: 'Funnel by stage',
                        description: 'Inspect deal distribution and monetary weight by pipeline stage.',
                    },
                },
                {
                    element: '[data-tour="reports-activity"]',
                    popover: {
                        title: 'Team activity',
                        description: 'See user-level activity counts to understand engagement.',
                    },
                },
            ],
        },
    },
    es: {
        next: 'Siguiente',
        prev: 'Atras',
        done: 'Finalizar',
        progressText: 'Paso {{current}} de {{total}}',
        modules: {
            dashboard: [
                {
                    element: '[data-tour="crm-sidebar"]',
                    popover: {
                        title: 'Navegacion CRM',
                        description: 'Usa este sidebar para moverte rapido entre todos los modulos del CRM.',
                    },
                },
                {
                    element: '[data-tour="dashboard-kpis"]',
                    popover: {
                        title: 'Metricas clave',
                        description: 'Estas tarjetas resumen deals abiertos, ganados, valor del pipeline y tareas pendientes.',
                    },
                },
                {
                    element: '[data-tour="dashboard-activity"]',
                    popover: {
                        title: 'Actividad reciente',
                        description: 'Revisa la linea de tiempo para entender los cambios recientes.',
                    },
                },
                {
                    element: '[data-tour="dashboard-tasks"]',
                    popover: {
                        title: 'Proximas tareas',
                        description: 'Controla lo siguiente por ejecutar y entra directo al centro de tareas.',
                    },
                },
            ],
            leads: [
                {
                    element: '[data-tour="leads-filters"]',
                    popover: {
                        title: 'Filtros de leads',
                        description: 'Filtra por texto y estado para enfocarte en las oportunidades correctas.',
                    },
                },
                {
                    element: '[data-tour="leads-table"]',
                    popover: {
                        title: 'Listado de leads',
                        description: 'Abre cualquier fila para ver detalle, actividades y conversion.',
                    },
                },
                {
                    element: '[data-tour="leads-create"]',
                    popover: {
                        title: 'Crear lead',
                        description: 'Usa esta accion para registrar un nuevo lead e iniciar su calificacion.',
                    },
                },
                {
                    element: '[data-tour="lead-details"]',
                    popover: {
                        title: 'Perfil del lead',
                        description: 'Consulta datos de origen, puntuacion y conversion en este panel.',
                    },
                },
                {
                    element: '[data-tour="lead-activity-form"]',
                    popover: {
                        title: 'Actividad del lead',
                        description: 'Registra notas, llamadas o reuniones para mantener contexto comercial.',
                    },
                },
            ],
            companies: [
                {
                    element: '[data-tour="companies-filters"]',
                    popover: {
                        title: 'Busqueda de empresas',
                        description: 'Busca por nombre, industria o correo para ubicar cuentas rapidamente.',
                    },
                },
                {
                    element: '[data-tour="companies-table"]',
                    popover: {
                        title: 'Listado de empresas',
                        description: 'Consulta contactos y deals relacionados desde esta tabla.',
                    },
                },
                {
                    element: '[data-tour="companies-create"]',
                    popover: {
                        title: 'Crear empresa',
                        description: 'Agrega una empresa para centralizar contexto de contactos y deals.',
                    },
                },
                {
                    element: '[data-tour="company-details"]',
                    popover: {
                        title: 'Detalle de empresa',
                        description: 'Revisa perfil, canales y contexto de negocio de la cuenta.',
                    },
                },
                {
                    element: '[data-tour="company-deals"]',
                    popover: {
                        title: 'Deals de la empresa',
                        description: 'Sigue oportunidades relacionadas y abre cada deal para gestion.',
                    },
                },
            ],
            contacts: [
                {
                    element: '[data-tour="contacts-filters"]',
                    popover: {
                        title: 'Busqueda de contactos',
                        description: 'Encuentra contactos por nombre, email o cargo.',
                    },
                },
                {
                    element: '[data-tour="contacts-table"]',
                    popover: {
                        title: 'Listado de contactos',
                        description: 'Abre un contacto para ver empresa, deals y actividad.',
                    },
                },
                {
                    element: '[data-tour="contacts-create"]',
                    popover: {
                        title: 'Crear contacto',
                        description: 'Registra nuevos decision makers y vincula su empresa.',
                    },
                },
                {
                    element: '[data-tour="contact-details"]',
                    popover: {
                        title: 'Detalle del contacto',
                        description: 'Consulta cargo, responsable y datos directos del contacto.',
                    },
                },
                {
                    element: '[data-tour="contact-deals"]',
                    popover: {
                        title: 'Deals del contacto',
                        description: 'Sigue las oportunidades vinculadas a este contacto.',
                    },
                },
            ],
            pipeline: [
                {
                    element: '[data-tour="pipeline-selector"]',
                    popover: {
                        title: 'Selector de pipeline',
                        description: 'Cambia de pipeline para revisar distintos flujos comerciales.',
                    },
                },
                {
                    element: '[data-tour="pipeline-board"]',
                    popover: {
                        title: 'Tablero Kanban',
                        description: 'Cada columna es una etapa. Mueve deals para mantener forecast y estado al dia.',
                    },
                },
            ],
            deals: [
                {
                    element: '[data-tour="deal-stage-panel"]',
                    popover: {
                        title: 'Progreso del deal',
                        description: 'Mueve etapas y cierra como ganado o perdido con trazabilidad completa.',
                    },
                },
                {
                    element: '[data-tour="deal-tasks"]',
                    popover: {
                        title: 'Tareas del deal',
                        description: 'Controla tareas y fechas de vencimiento asociadas al deal.',
                    },
                },
                {
                    element: '[data-tour="deal-activity-form"]',
                    popover: {
                        title: 'Registro de actividad',
                        description: 'Guarda llamadas, emails, notas y reuniones en la cronologia del deal.',
                    },
                },
            ],
            tasks: [
                {
                    element: '[data-tour="tasks-filters"]',
                    popover: {
                        title: 'Filtros de tareas',
                        description: 'Filtra por estado y prioridad para dirigir la ejecucion diaria.',
                    },
                },
                {
                    element: '[data-tour="tasks-table"]',
                    popover: {
                        title: 'Listado de tareas',
                        description: 'Completa pendientes y monitorea asignaciones desde un solo lugar.',
                    },
                },
                {
                    element: '[data-tour="tasks-create"]',
                    popover: {
                        title: 'Crear tarea',
                        description: 'Planifica seguimientos y responsables con fecha de vencimiento.',
                    },
                },
            ],
            reports: [
                {
                    element: '[data-tour="reports-kpis"]',
                    popover: {
                        title: 'KPIs de rendimiento',
                        description: 'Monitorea conversion, valor ganado, valor abierto y volumen de leads.',
                    },
                },
                {
                    element: '[data-tour="reports-funnel"]',
                    popover: {
                        title: 'Embudo por etapa',
                        description: 'Analiza distribucion de deals y monto por etapa del pipeline.',
                    },
                },
                {
                    element: '[data-tour="reports-activity"]',
                    popover: {
                        title: 'Actividad del equipo',
                        description: 'Consulta la actividad por usuario para medir engagement.',
                    },
                },
            ],
        },
    },
};

let activeDriver = null;
let listenersBound = false;
let isStarting = false;
const autoStarted = new Set();

function getLocale() {
    const locale = document.body?.dataset.crmLocale ?? document.documentElement.lang ?? 'en';
    return locale.toLowerCase().startsWith('es') ? 'es' : 'en';
}

function getCopy() {
    return I18N[getLocale()] ?? I18N.en;
}

function getRouteName() {
    return document.body?.dataset.currentRouteName ?? '';
}

function resolveModule(routeName) {
    for (const [pattern, module] of ROUTE_TO_MODULE) {
        if (pattern.test(routeName)) {
            return module;
        }
    }

    return null;
}

function getApiUrls() {
    return {
        stateUrl: document.body?.dataset.crmTutorialStateUrl ?? '',
        updateUrl: document.body?.dataset.crmTutorialUpdateUrl ?? '',
        restartUrl: document.body?.dataset.crmTutorialRestartUrl ?? '',
    };
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            ...(options.headers ?? {}),
        },
        ...options,
    });

    if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
    }

    return response.json();
}

async function postJson(url, payload = {}) {
    return fetchJson(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(payload),
    });
}

function collectSteps(moduleKey) {
    const copy = getCopy();
    const moduleSteps = copy.modules[moduleKey] ?? [];

    return moduleSteps.filter((step) => {
        if (!step.element || typeof step.element !== 'string') {
            return true;
        }

        return document.querySelector(step.element) !== null;
    });
}

async function startTourForCurrentRoute({ manualRestart = false } = {}) {
    if (isStarting) {
        return;
    }

    const routeName = getRouteName();
    if (!routeName.startsWith('crm.')) {
        return;
    }

    const moduleKey = resolveModule(routeName);
    if (!moduleKey) {
        return;
    }

    const { stateUrl, updateUrl, restartUrl } = getApiUrls();
    if (stateUrl === '' || updateUrl === '' || restartUrl === '') {
        return;
    }

    if (activeDriver?.isActive()) {
        activeDriver.destroy();
    }

    isStarting = true;

    try {
        if (manualRestart) {
            await postJson(restartUrl);
            autoStarted.delete(`${routeName}:${moduleKey}`);
        }

        const state = await fetchJson(stateUrl);
        const completedModules = state.completed_modules ?? [];

        if (!manualRestart) {
            if (state.dismissed || completedModules.includes(moduleKey)) {
                return;
            }

            const autoStartKey = `${routeName}:${moduleKey}`;
            if (autoStarted.has(autoStartKey)) {
                return;
            }

            autoStarted.add(autoStartKey);
        }

        const steps = collectSteps(moduleKey);
        if (steps.length === 0) {
            return;
        }

        const copy = getCopy();

        activeDriver = driver({
            animate: true,
            smoothScroll: true,
            allowClose: true,
            showProgress: true,
            progressText: copy.progressText,
            nextBtnText: copy.next,
            prevBtnText: copy.prev,
            doneBtnText: copy.done,
            steps,
            onNextClick: (_element, _step, options) => {
                if (options.driver.isLastStep()) {
                    void postJson(updateUrl, {
                        action: 'complete-module',
                        module: moduleKey,
                    });

                    options.driver.destroy();
                    return;
                }

                options.driver.moveNext();
            },
            onCloseClick: (_element, _step, options) => {
                if (!manualRestart) {
                    void postJson(updateUrl, { action: 'skip' });
                }

                options.driver.destroy();
            },
        });

        activeDriver.drive();
    } catch (error) {
        console.warn('[crm-tour] Unable to start tutorial.', error);
    } finally {
        isStarting = false;
    }
}

function bindListeners() {
    if (listenersBound) {
        return;
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-tour-launcher]');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        void startTourForCurrentRoute({ manualRestart: true });
    });

    window.addEventListener('crm-tour:restart', () => {
        void startTourForCurrentRoute({ manualRestart: true });
    });

    listenersBound = true;
}

export function bootCrmTour() {
    bindListeners();
    void startTourForCurrentRoute();
}

bootCrmTour();
document.addEventListener('livewire:navigated', () => {
    void startTourForCurrentRoute();
});
