import type { ClockRequest } from "../../../types/clock.types";
import Table from "../../common/Table";
import { EditSquareIcon } from "../../../assets/icons/edit-square";

interface ClockRequestListProps {
  requests: ClockRequest[];
  onEdit: (request: ClockRequest) => void;
}

export default function ClockRequestList({
  requests,
  onEdit,
}: ClockRequestListProps) {
  const getStatusBadge = (status: string) => {
    const statusConfig = {
      PENDING: { label: "En attente", bgColor: "var(--status-pending)" },
      APPROVED: { label: "Approuvé", bgColor: "var(--status-approved)" },
      REJECTED: { label: "Rejeté", bgColor: "var(--status-rejected)" },
    };
    const config =
      statusConfig[status as keyof typeof statusConfig] || statusConfig.PENDING;
    return (
      <span
        className="text-sm font-medium text-white px-3 py-1 rounded-full inline-block w-fit"
        style={{ backgroundColor: config.bgColor }}
      >
        {config.label}
      </span>
    );
  };

  const columns = [
    {
      header: "Employé",
      accessor: (request: ClockRequest) => (
        <div className="flex flex-col">
          <span className="font-medium">
            {request.user.firstName} {request.user.lastName}
          </span>
          <span className="text-xs text-[var(--c4)]">{request.user.email}</span>
        </div>
      ),
    },
    {
      header: "Date",
      accessor: (request: ClockRequest) =>
        new Date(request.requestedTime).toLocaleDateString("fr-FR"),
    },
    {
      header: "Heure",
      accessor: (request: ClockRequest) =>
        new Date(request.requestedTime).toLocaleTimeString("fr-FR", {
          hour: "2-digit",
          minute: "2-digit",
        }),
    },
    {
      header: "Type",
      accessor: (request: ClockRequest) => (
        <span className="text-sm font-medium text-[var(--c1)] bg-[var(--c4)] px-2 py-0.5 rounded-full inline-block w-fit">
          {request.requestedStatus ? "↓ Entrée" : "↑ Sortie"}
        </span>
      ),
    },
    {
      header: "Statut",
      accessor: (request: ClockRequest) => getStatusBadge(request.status),
    },
    {
      header: "Raison",
      accessor: (request: ClockRequest) => (
        <div className="max-w-xs truncate" title={request.reason}>
          {request.reason || "-"}
        </div>
      ),
    },
    {
      header: "Actions",
      accessor: (request: ClockRequest) => (
        <div className="flex gap-2">
          <EditSquareIcon
            className="h-7 w-7 p-1 cursor-pointer hover:bg-[var(--c1)] rounded"
            onClick={() => onEdit(request)}
            title="Modifier/Examiner"
          />
        </div>
      ),
    },
  ];

  return <Table data={requests} columns={columns} emptyMessage="Aucune demande de pointage trouvée" />;
}
